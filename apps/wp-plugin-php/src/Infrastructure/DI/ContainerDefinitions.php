<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\DI;

use Baywall\Core\Application\Service\AccessTokenExpirationProvider;
use Baywall\Core\Application\Service\AccessTokenRequestProvider;
use Baywall\Core\Application\Service\AccessTokenCookieProvider;
use Baywall\Core\Application\Repository\Erc4361NonceRepository;
use Baywall\Core\Application\Repository\JwtSecretKeyRepository;
use Baywall\Core\Application\Repository\PurgeOnUninstallRepository;
use Baywall\Core\Application\Repository\SctaUrlRepository;
use Baywall\Core\Application\Repository\ThemeSettingRepository;
use Baywall\Core\Application\Service\Erc4361NonceProvider;
use Baywall\Core\Application\Service\Erc4361PropertyProvider;
use Baywall\Core\Application\Service\GraphQLService;
use Baywall\Core\Application\Service\InvoiceTokenCookieProvider;
use Baywall\Core\Application\Service\JwtAlgorithmProvider;
use Baywall\Core\Application\Service\LockService;
use Baywall\Core\Application\Service\LogQueryService;
use Baywall\Core\Application\Service\PaidContentService;
use Baywall\Core\Application\Service\PluginMigrationService;
use Baywall\Core\Application\Service\PluginTeardownService;
use Baywall\Core\Application\Service\RefreshTokenCookieProvider;
use Baywall\Core\Application\Service\SalesHistoryQueryService;
use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Application\Service\UserAccessProvider;
use Baywall\Core\Domain\Repository\AppContractRepository;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\InvoiceRepository;
use Baywall\Core\Domain\Repository\InvoiceTokenRepository;
use Baywall\Core\Domain\Repository\InstallOriginUrl;
use Baywall\Core\Domain\Repository\NetworkCategoryRepository;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\Repository\PausedRepository;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\Repository\RefreshTokenRepository;
use Baywall\Core\Domain\Repository\SellerRepository;
use Baywall\Core\Domain\Repository\ServerSignerRepository;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\Service\AppContractDataProvider;
use Baywall\Core\Domain\Service\BlockNumberProvider;
use Baywall\Core\Domain\Service\CookieNameProvider;
use Baywall\Core\Domain\Service\InvoiceTokenProvider;
use Baywall\Core\Domain\Service\PluginInfoProvider;
use Baywall\Core\Domain\Service\PostTitleProvider;
use Baywall\Core\Domain\Service\RateProvider;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\Service\SiteService;
use Baywall\Core\Infrastructure\Cache\OracleRateCache;
use Baywall\Core\Infrastructure\Content\PaidContentServiceImpl;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpAppContractRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpChainRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpInvoiceRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpOracleRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpPostRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpTokenRepository;
use Baywall\Core\Infrastructure\Logging\Handler\SimpleLogger;
use Baywall\Core\Infrastructure\Logging\Logger;
use Baywall\Core\Infrastructure\WordPress\Logging\WpDatabaseLogger;
use Baywall\Core\Infrastructure\Logging\LogLevelRepository;
use Baywall\Core\Infrastructure\Web3\Service\AppContractDataProviderImpl;
use Baywall\Core\Infrastructure\Web3\Service\BlockNumberProviderImpl;
use Baywall\Core\Infrastructure\Web3\Service\CachedOracleRateProvider;
use Baywall\Core\Infrastructure\WordPress\Cache\WpOracleRateCache;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpErc4361NonceRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpInvoiceTokenRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpNetworkCategoryRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpRefreshTokenRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpSellerRepository;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpServerSignerRepository;
use Baywall\Core\Infrastructure\WordPress\Repository\WpInstallOriginUrl;
use Baywall\Core\Infrastructure\WordPress\Repository\WpPausedRepository;
use Baywall\Core\Infrastructure\WordPress\Repository\WpPurgeOnUninstallRepository;
use Baywall\Core\Infrastructure\WordPress\Repository\WpSctaUrlRepository;
use Baywall\Core\Infrastructure\WordPress\Repository\WpThemeSettingRepository;
use Baywall\Core\Infrastructure\WordPress\Repository\WpLogLevelRepository;
use Baywall\Core\Infrastructure\WordPress\Service\WpAccessTokenExpirationProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpAccessTokenCookieProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpCookieNameProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpAccessTokenRequestProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpAppManifestFetcher;
use Baywall\Core\Infrastructure\WordPress\Service\WpErc4361NonceProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpErc4361PropertyProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpGraphQLService;
use Baywall\Core\Infrastructure\WordPress\Service\WpInvoiceTokenCookieProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpInvoiceTokenProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpJwtAlgorithmProvider;
use Baywall\Core\Infrastructure\WordPress\Repository\WpJwtSecretKeyRepository;
use Baywall\Core\Infrastructure\WordPress\Service\WpLockService;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginInfoProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginPackageChecksumVerifier;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginUpdateChecker;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginMigrationService;
use Baywall\Core\Infrastructure\WordPress\Service\WpPostTitleProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginTeardownService;
use Baywall\Core\Infrastructure\WordPress\Service\WpRefreshTokenCookieProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpRefreshTokenService;
use Baywall\Core\Infrastructure\WordPress\Service\WpSiteService;
use Baywall\Core\Infrastructure\WordPress\Service\WpUserAccessProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpLogQueryService;
use Baywall\Core\Infrastructure\WordPress\Service\WpSalesHistoryQueryService;
use Baywall\Core\Infrastructure\WordPress\Service\WpTransactionService;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressCoreLatestVersionService;
use Baywall\Core\Infrastructure\WordPress\Service\WpWordPressCoreLatestVersionService;
use wpdb;

use function DI\autowire;
use function DI\get;

final class ContainerDefinitions {
	public static function getDefinitions(): array {
		return array(
			wpdb::class                              => fn() => $GLOBALS['wpdb'],

			// TableGateway
			// ChainTable::class => autowire(),

			// Repository
			AppContractRepository::class             => autowire( WpAppContractRepository::class ),
			ChainRepository::class                   => autowire( WpChainRepository::class ),
			InvoiceRepository::class                 => autowire( WpInvoiceRepository::class ),
			NetworkCategoryRepository::class         => autowire( WpNetworkCategoryRepository::class ),
			OracleRepository::class                  => autowire( WpOracleRepository::class ),
			PostRepository::class                    => autowire( WpPostRepository::class ),
			ServerSignerRepository::class            => autowire( WpServerSignerRepository::class ),
			TokenRepository::class                   => autowire( WpTokenRepository::class ),
			SellerRepository::class                  => autowire( WpSellerRepository::class ),
			RefreshTokenRepository::class            => autowire( WpRefreshTokenRepository::class ),
			InvoiceTokenRepository::class            => autowire( WpInvoiceTokenRepository::class ),
			Erc4361NonceRepository::class            => autowire( WpErc4361NonceRepository::class ),
			PausedRepository::class                  => autowire( WpPausedRepository::class ),
			SctaUrlRepository::class                 => autowire( WpSctaUrlRepository::class ),
			ThemeSettingRepository::class            => autowire( WpThemeSettingRepository::class ),
			PurgeOnUninstallRepository::class        => autowire( WpPurgeOnUninstallRepository::class ),
			InstallOriginUrl::class                  => autowire( WpInstallOriginUrl::class ),
			JwtSecretKeyRepository::class            => autowire( WpJwtSecretKeyRepository::class ),

			// Service
			PostTitleProvider::class                 => autowire( WpPostTitleProvider::class ),
			RateProvider::class                      => get( CachedOracleRateProvider::class ),
			// CachedRateProvider::class    => autowire()->constructor(
			// get( RateTransient::class ),
			// get( OracleRateProviderImpl::class )
			// ),
			UserAccessProvider::class                => autowire( WpUserAccessProvider::class ),
			PaidContentService::class                => autowire( PaidContentServiceImpl::class ),
			AppContractDataProvider::class           => autowire( AppContractDataProviderImpl::class ),
			BlockNumberProvider::class               => autowire( BlockNumberProviderImpl::class ),
			TransactionService::class                => autowire( WpTransactionService::class ),
			LockService::class                       => autowire( WpLockService::class ),
			SalesHistoryQueryService::class          => autowire( WpSalesHistoryQueryService::class ),
			LogQueryService::class                   => autowire( WpLogQueryService::class ),
			JwtAlgorithmProvider::class              => autowire( WpJwtAlgorithmProvider::class ),
			AccessTokenExpirationProvider::class     => autowire( WpAccessTokenExpirationProvider::class ),
			AccessTokenRequestProvider::class        => autowire( WpAccessTokenRequestProvider::class ),
			CookieNameProvider::class                => autowire( WpCookieNameProvider::class ),
			AccessTokenCookieProvider::class         => autowire( WpAccessTokenCookieProvider::class ),
			RefreshTokenCookieProvider::class        => autowire( WpRefreshTokenCookieProvider::class ),
			InvoiceTokenCookieProvider::class        => autowire( WpInvoiceTokenCookieProvider::class ),
			RefreshTokenService::class               => autowire( WpRefreshTokenService::class ),
			InvoiceTokenProvider::class              => autowire( WpInvoiceTokenProvider::class ),
			Erc4361PropertyProvider::class           => autowire( WpErc4361PropertyProvider::class ),
			Erc4361NonceProvider::class              => autowire( WpErc4361NonceProvider::class ),
			GraphQLService::class                    => autowire( WpGraphQLService::class ),
			SiteService::class                       => autowire( WpSiteService::class ),
			PluginInfoProvider::class                => autowire( WpPluginInfoProvider::class ),
			PluginMigrationService::class            => autowire( WpPluginMigrationService::class ),
			PluginTeardownService::class             => autowire( WpPluginTeardownService::class ),
			WordPressCoreLatestVersionService::class => autowire( WpWordPressCoreLatestVersionService::class ),
			// プラグイン自動アップデートチェック用
			WpAppManifestFetcher::class              => autowire(),
			WpPluginUpdateChecker::class             => autowire(),
			WpPluginPackageChecksumVerifier::class   => autowire(),

			// Cache
			OracleRateCache::class                   => autowire( WpOracleRateCache::class ),

			// Logging
			Logger::class                            => autowire( WpDatabaseLogger::class ),
			SimpleLogger::class                      => autowire(),
			LogLevelRepository::class                => autowire( WpLogLevelRepository::class ),
		);
	}
}
