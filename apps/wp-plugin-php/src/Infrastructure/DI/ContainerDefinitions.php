<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\DI;

use Baywall\Core\Application\Service\AccessTokenExpirationProvider;
use Baywall\Core\Application\Service\Clock;
use Baywall\Core\Application\Service\CookieWriter;
use Baywall\Core\Application\Service\AccessTokenRequestProvider;
use Baywall\Core\Application\Service\AccessTokenCookieProvider;
use Baywall\Core\Application\Service\AdminNotificationService;
use Baywall\Core\Application\Service\AppContractClientFactory;
use Baywall\Core\Application\Service\CrawlFailureNotificationMessageProvider;
use Baywall\Core\Application\Repository\Erc4361NonceRepository;
use Baywall\Core\Application\Repository\InstalledPluginVersionRepository;
use Baywall\Core\Application\Repository\JwtSecretKeyRepository;
use Baywall\Core\Application\Repository\LogRepository;
use Baywall\Core\Application\Repository\PurgeOnUninstallRepository;
use Baywall\Core\Application\Repository\SctaUrlRepository;
use Baywall\Core\Application\Repository\ThemeSettingRepository;
use Baywall\Core\Application\Service\Erc4361NonceGenerator;
use Baywall\Core\Application\Service\Erc4361PropertyProvider;
use Baywall\Core\Application\Service\GraphQLConfigProvider;
use Baywall\Core\Application\Service\InvoiceTokenCookieProvider;
use Baywall\Core\Application\Service\JwtAlgorithmProvider;
use Baywall\Core\Application\Service\JwtService;
use Baywall\Core\Application\Service\LockService;
use Baywall\Core\Application\Service\LogQueryService;
use Baywall\Core\Application\Service\PaidContentAnalyzer;
use Baywall\Core\Application\Service\PluginAutoUpdateStateProvider;
use Baywall\Core\Application\Service\PluginDeactivationService;
use Baywall\Core\Application\Service\PluginMigrationService;
use Baywall\Core\Application\Service\PluginCompatibilityChecker;
use Baywall\Core\Application\Service\PluginTeardownService;
use Baywall\Core\Application\Service\RefreshTokenCookieProvider;
use Baywall\Core\Application\Service\SalesHistoryQueryService;
use Baywall\Core\Application\Service\SupportedVersionConstraintsProvider;
use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Application\Service\UserAccessProvider;
use Baywall\Core\Domain\Repository\AppContractRepository;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\CrawledBlockRepository;
use Baywall\Core\Domain\Repository\InvoiceRepository;
use Baywall\Core\Domain\Repository\InvoiceTokenRepository;
use Baywall\Core\Domain\Repository\InitialSiteUrlRepository;
use Baywall\Core\Domain\Repository\NetworkCategoryRepository;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\Repository\PausedRepository;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\Repository\RefreshTokenRepository;
use Baywall\Core\Domain\Repository\SellerRepository;
use Baywall\Core\Domain\Repository\ServerSignerRepository;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\Repository\UnlockPaywallTransferEventRepository;
use Baywall\Core\Domain\Service\AppContractDataProvider;
use Baywall\Core\Domain\Service\BlockchainService;
use Baywall\Core\Domain\Service\CookieNameProvider;
use Baywall\Core\Domain\Service\InvoiceTokenGenerator;
use Baywall\Core\Domain\Service\OracleService;
use Baywall\Core\Domain\Service\PluginInfoProvider;
use Baywall\Core\Domain\Service\RateProvider;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\Service\SiteUrlChecker;
use Baywall\Core\Infrastructure\Cache\OracleRateCache;
use Baywall\Core\Infrastructure\Cookie\CookieWriterImpl;
use Baywall\Core\Infrastructure\JWT\JwtServiceImpl;
use Baywall\Core\Infrastructure\System\SystemClock;
use Baywall\Core\Infrastructure\Util\NamespaceParser;
use Baywall\Core\Infrastructure\Content\PaidContentAnalyzerImpl;
use Baywall\Core\Infrastructure\WordPress\Database\Migration\MigrationLocator;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpAppContractRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpChainRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpCrawledBlockRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpInvoiceRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpLogRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpOracleRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpPostRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpTokenRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpUnlockPaywallTransferEventRepository;
use Baywall\Core\Infrastructure\Logging\ErrorLogLogger;
use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Logging\Logger;
use Baywall\Core\Infrastructure\WordPress\Logging\WpDatabaseLogger;
use Baywall\Core\Application\Logging\LogLevelRepository;
use Baywall\Core\Infrastructure\Web3\Factory\AppContractClientFactoryImpl;
use Baywall\Core\Infrastructure\Web3\Service\AppContractDataProviderImpl;
use Baywall\Core\Infrastructure\Web3\Service\BlockchainServiceImpl;
use Baywall\Core\Infrastructure\Web3\Service\EnsService;
use Baywall\Core\Infrastructure\Web3\Service\OracleServiceImpl;
use Baywall\Core\Infrastructure\Web3\Service\RateProviderImpl;
use Baywall\Core\Infrastructure\WordPress\Cache\WpOracleRateCache;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpErc4361NonceRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpInvoiceTokenRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpNetworkCategoryRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpRefreshTokenRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpSellerRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpServerSignerRepository;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpInitialSiteUrlRepository;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpInstalledPluginVersionRepository;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpPausedRepository;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpPurgeOnUninstallRepository;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpSctaUrlRepository;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpThemeSettingRepository;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpLogLevelRepository;
use Baywall\Core\Infrastructure\WordPress\Auth\WpAccessTokenExpirationProvider;
use Baywall\Core\Infrastructure\WordPress\Cookie\WpAccessTokenCookieProvider;
use Baywall\Core\Infrastructure\WordPress\Cookie\WpCookieNameProvider;
use Baywall\Core\Infrastructure\WordPress\Auth\WpAccessTokenRequestProvider;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Infrastructure\WordPress\Service\WpAdminNotificationService;
use Baywall\Core\Infrastructure\WordPress\Service\WpAppManifestFetcher;
use Baywall\Core\Infrastructure\WordPress\Service\WpCrawlFailureNotificationMessageProvider;
use Baywall\Core\Infrastructure\WordPress\Auth\WpErc4361NonceGenerator;
use Baywall\Core\Infrastructure\WordPress\Auth\WpErc4361PropertyProvider;
use Baywall\Core\Infrastructure\GraphQL\WpGraphQLConfigProvider;
use Baywall\Core\Infrastructure\WordPress\Cookie\WpInvoiceTokenCookieProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpInvoiceTokenGenerator;
use Baywall\Core\Infrastructure\WordPress\Auth\WpJwtAlgorithmProvider;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpJwtSecretKeyRepository;
use Baywall\Core\Infrastructure\WordPress\Service\WpLockService;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginAssetUrlProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginAutoUpdateStateProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginDeactivationService;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginInfoProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginPackageChecksumVerifier;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginUpdateChecker;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginMigrationService;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginCompatibilityChecker;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpSupportedVersionConstraintsProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginTeardownService;
use Baywall\Core\Infrastructure\WordPress\Cookie\WpRefreshTokenCookieProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpRefreshTokenService;
use Baywall\Core\Infrastructure\WordPress\Service\WpSiteUrlChecker;
use Baywall\Core\Infrastructure\WordPress\Query\WpUserAccessProvider;
use Baywall\Core\Infrastructure\WordPress\Query\WpLogQueryService;
use Baywall\Core\Infrastructure\WordPress\Query\WpSalesHistoryQueryService;
use Baywall\Core\Infrastructure\WordPress\Service\WpTransactionService;
use Baywall\Core\Presentation\GraphQL\RootValue;
use Baywall\Core\Presentation\GraphQL\Resolvers\Resolver;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveChain;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveChains;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveIssueAccessTokenByErc4361Signature;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveIssueErc4361Message;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveIssueInvoiceV2;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveLogs;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveNativeToken;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveNetworkCategories;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveNetworkCategory;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveOracle;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveOracles;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePayableTokens;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePaused;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePost;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePurgeOnUninstall;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSalesHistories;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSalesHistoriesCount;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveChain;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveOracle;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveSeller;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveSiteSettings;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveToken;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSctaUrl;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSeller;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSellingContent;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSellingPrice;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveServerSigner;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSiteUrlChangedSinceInstall;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveThemeSetting;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveToken;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveTokens;
use Psr\Container\ContainerInterface;
use wpdb;

use function DI\autowire;
use function DI\get;

final class ContainerDefinitions {
	public static function getDefinitions(): array {
		return array(
			wpdb::class                                    => fn() => $GLOBALS['wpdb'],

			// TableGateway
			// ChainTable::class => autowire(),

			// Repository
			AppContractRepository::class                   => autowire( WpAppContractRepository::class ),
			CrawledBlockRepository::class                  => autowire( WpCrawledBlockRepository::class ),
			ChainRepository::class                         => autowire( WpChainRepository::class ),
			InvoiceRepository::class                       => autowire( WpInvoiceRepository::class ),
			NetworkCategoryRepository::class               => autowire( WpNetworkCategoryRepository::class ),
			OracleRepository::class                        => autowire( WpOracleRepository::class ),
			PostRepository::class                          => autowire( WpPostRepository::class ),
			ServerSignerRepository::class                  => autowire( WpServerSignerRepository::class ),
			TokenRepository::class                         => autowire( WpTokenRepository::class ),
			SellerRepository::class                        => autowire( WpSellerRepository::class ),
			RefreshTokenRepository::class                  => autowire( WpRefreshTokenRepository::class ),
			InvoiceTokenRepository::class                  => autowire( WpInvoiceTokenRepository::class ),
			Erc4361NonceRepository::class                  => autowire( WpErc4361NonceRepository::class ),
			PausedRepository::class                        => autowire( WpPausedRepository::class ),
			SctaUrlRepository::class                       => autowire( WpSctaUrlRepository::class ),
			ThemeSettingRepository::class                  => autowire( WpThemeSettingRepository::class ),
			PurgeOnUninstallRepository::class              => autowire( WpPurgeOnUninstallRepository::class ),
			InitialSiteUrlRepository::class                => autowire( WpInitialSiteUrlRepository::class ),
			JwtSecretKeyRepository::class                  => autowire( WpJwtSecretKeyRepository::class ),
			LogRepository::class                           => autowire( WpLogRepository::class ),
			UnlockPaywallTransferEventRepository::class    => autowire( WpUnlockPaywallTransferEventRepository::class ),
			InstalledPluginVersionRepository::class        => autowire( WpInstalledPluginVersionRepository::class ),

			// Service
			Clock::class                                   => autowire( SystemClock::class ),
			CookieWriter::class                            => autowire( CookieWriterImpl::class ),
			RateProvider::class                            => get( RateProviderImpl::class ),
			UserAccessProvider::class                      => autowire( WpUserAccessProvider::class ),
			PaidContentAnalyzer::class                     => autowire( PaidContentAnalyzerImpl::class ),
			AppContractDataProvider::class                 => autowire( AppContractDataProviderImpl::class ),
			AppContractClientFactory::class                => autowire( AppContractClientFactoryImpl::class ),
			OracleService::class                           => autowire( OracleServiceImpl::class ),
			BlockchainService::class                       => autowire( BlockchainServiceImpl::class ),
			EnsService::class                              => autowire(),
			TransactionService::class                      => autowire( WpTransactionService::class ),
			LockService::class                             => autowire( WpLockService::class ),
			SalesHistoryQueryService::class                => autowire( WpSalesHistoryQueryService::class ),
			LogQueryService::class                         => autowire( WpLogQueryService::class ),
			JwtAlgorithmProvider::class                    => autowire( WpJwtAlgorithmProvider::class ),
			JwtService::class                              => autowire( JwtServiceImpl::class ),
			AccessTokenExpirationProvider::class           => autowire( WpAccessTokenExpirationProvider::class ),
			AccessTokenRequestProvider::class              => autowire( WpAccessTokenRequestProvider::class ),
			CookieNameProvider::class                      => autowire( WpCookieNameProvider::class ),
			AccessTokenCookieProvider::class               => autowire( WpAccessTokenCookieProvider::class ),
			RefreshTokenCookieProvider::class              => autowire( WpRefreshTokenCookieProvider::class ),
			InvoiceTokenCookieProvider::class              => autowire( WpInvoiceTokenCookieProvider::class ),
			RefreshTokenService::class                     => autowire( WpRefreshTokenService::class ),
			InvoiceTokenGenerator::class                   => autowire( WpInvoiceTokenGenerator::class ),
			Erc4361PropertyProvider::class                 => autowire( WpErc4361PropertyProvider::class ),
			Erc4361NonceGenerator::class                   => autowire( WpErc4361NonceGenerator::class ),
			GraphQLConfigProvider::class                   => autowire( WpGraphQLConfigProvider::class ),
			SiteUrlChecker::class                          => autowire( WpSiteUrlChecker::class ),
			PluginDeactivationService::class               => autowire( WpPluginDeactivationService::class ),
			PluginAutoUpdateStateProvider::class           => autowire( WpPluginAutoUpdateStateProvider::class ),
			PluginInfoProvider::class                      => autowire( WpPluginInfoProvider::class ),
			WpPluginAssetUrlProvider::class                => autowire(),
			PluginMigrationService::class                  => autowire( WpPluginMigrationService::class ),
			PluginCompatibilityChecker::class              => autowire( WpPluginCompatibilityChecker::class ),
			PluginTeardownService::class                   => autowire( WpPluginTeardownService::class ),
			SupportedVersionConstraintsProvider::class     => autowire( WpSupportedVersionConstraintsProvider::class )
				->constructorParameter( 'wp_versions_json_path', WpConfig::WP_VERSIONS_JSON_PATH )
				->constructorParameter( 'php_versions_json_path', WpConfig::PHP_VERSIONS_JSON_PATH ),
			AdminNotificationService::class                => autowire( WpAdminNotificationService::class ),
			CrawlFailureNotificationMessageProvider::class => autowire( WpCrawlFailureNotificationMessageProvider::class ),

			// Presentation
			RootValue::class                               => function ( ContainerInterface $container ) {
				/** @var array<string, \Closure(): Resolver> フィールド名 → resolver 生成 closure の対応表 */
				$resolvers = array(
					// 非公開
					'chain'                              => fn() => $container->get( ResolveChain::class ),
					'oracle'                             => fn() => $container->get( ResolveOracle::class ),
					'networkCategory'                    => fn() => $container->get( ResolveNetworkCategory::class ),
					'sellingContent'                     => fn() => $container->get( ResolveSellingContent::class ),
					'sellingPrice'                       => fn() => $container->get( ResolveSellingPrice::class ),
					'token'                              => fn() => $container->get( ResolveToken::class ),

					// Query
					'chains'                             => fn() => $container->get( ResolveChains::class ),
					'networkCategories'                  => fn() => $container->get( ResolveNetworkCategories::class ),
					'oracles'                            => fn() => $container->get( ResolveOracles::class ),
					'payableTokens'                      => fn() => $container->get( ResolvePayableTokens::class ),
					'post'                               => fn() => $container->get( ResolvePost::class ),
					'salesHistories'                     => fn() => $container->get( ResolveSalesHistories::class ),
					'salesHistoriesCount'                => fn() => $container->get( ResolveSalesHistoriesCount::class ),
					'seller'                             => fn() => $container->get( ResolveSeller::class ),
					'serverSigner'                       => fn() => $container->get( ResolveServerSigner::class ),
					'tokens'                             => fn() => $container->get( ResolveTokens::class ),
					'nativeToken'                        => fn() => $container->get( ResolveNativeToken::class ),
					'paused'                             => fn() => $container->get( ResolvePaused::class ),
					'sctaUrl'                            => fn() => $container->get( ResolveSctaUrl::class ),
					'themeSetting'                       => fn() => $container->get( ResolveThemeSetting::class ),
					'purgeOnUninstall'                   => fn() => $container->get( ResolvePurgeOnUninstall::class ),
					'siteUrlChangedSinceInstall'         => fn() => $container->get( ResolveSiteUrlChangedSinceInstall::class ),
					'logs'                               => fn() => $container->get( ResolveLogs::class ),

					// Mutation
					'issueInvoiceV2'                     => fn() => $container->get( ResolveIssueInvoiceV2::class ),
					'issueErc4361Message'                => fn() => $container->get( ResolveIssueErc4361Message::class ),
					'issueAccessTokenByErc4361Signature' => fn() => $container->get( ResolveIssueAccessTokenByErc4361Signature::class ),
					'saveChain'                          => fn() => $container->get( ResolveSaveChain::class ),
					'saveOracle'                         => fn() => $container->get( ResolveSaveOracle::class ),
					'saveSeller'                         => fn() => $container->get( ResolveSaveSeller::class ),
					'saveSiteSettings'                   => fn() => $container->get( ResolveSaveSiteSettings::class ),
					'saveToken'                          => fn() => $container->get( ResolveSaveToken::class ),
				);
				return new RootValue( $resolvers, $container->get( AppLogger::class ) );
			},

			// プラグイン自動アップデートチェック用
			WpAppManifestFetcher::class                    => autowire(),
			WpPluginUpdateChecker::class                   => autowire(),
			WpPluginPackageChecksumVerifier::class         => autowire(),

			// Migration
			MigrationLocator::class                        => fn( ContainerInterface $c ) => new MigrationLocator(
				$c->get( NamespaceParser::class ),
				fn( string $class_name ) => $c->get( $class_name )
			),

			// Cache
			OracleRateCache::class                         => autowire( WpOracleRateCache::class ),

			// Logging
			Logger::class                                  => autowire( WpDatabaseLogger::class ),
			ErrorLogLogger::class                          => autowire(),
			LogLevelRepository::class                      => autowire( WpLogLevelRepository::class ),
		);
	}
}
