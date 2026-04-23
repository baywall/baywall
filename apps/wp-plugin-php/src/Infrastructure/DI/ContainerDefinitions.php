<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\DI;

use Cornix\Serendipity\Core\Application\Service\AccessTokenExpirationProvider;
use Cornix\Serendipity\Core\Application\Service\AccessTokenRequestProvider;
use Cornix\Serendipity\Core\Application\Service\AccessTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Repository\Erc4361NonceRepository;
use Cornix\Serendipity\Core\Application\Repository\JwtSecretKeyRepository;
use Cornix\Serendipity\Core\Application\Repository\SctaUrlRepository;
use Cornix\Serendipity\Core\Application\Service\Erc4361NonceProvider;
use Cornix\Serendipity\Core\Application\Service\Erc4361PropertyProvider;
use Cornix\Serendipity\Core\Application\Service\InvoiceTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\JwtAlgorithmProvider;
use Cornix\Serendipity\Core\Application\Service\LockService;
use Cornix\Serendipity\Core\Application\Service\PaidContentService;
use Cornix\Serendipity\Core\Application\Service\PluginMigrationService;
use Cornix\Serendipity\Core\Application\Service\PluginTeardownService;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\SalesHistoryQueryService;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessProvider;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceTokenRepository;
use Cornix\Serendipity\Core\Domain\Repository\InstallOriginUrl;
use Cornix\Serendipity\Core\Domain\Repository\NetworkCategoryRepository;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Repository\PausedRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\RefreshTokenRepository;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\AppContractDataProvider;
use Cornix\Serendipity\Core\Domain\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Domain\Service\CookieNameProvider;
use Cornix\Serendipity\Core\Domain\Service\InvoiceTokenProvider;
use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;
use Cornix\Serendipity\Core\Domain\Service\RateProvider;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\Service\SiteService;
use Cornix\Serendipity\Core\Infrastructure\Cache\OracleRateCache;
use Cornix\Serendipity\Core\Infrastructure\Content\PaidContentServiceImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpAppContractRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpChainRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpInvoiceRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpOracleRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpPostRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpTokenRepository;
use Cornix\Serendipity\Core\Infrastructure\Logging\Handler\SimpleLogger;
use Cornix\Serendipity\Core\Infrastructure\Logging\Logger;
use Cornix\Serendipity\Core\Infrastructure\Logging\LogLevelRepository;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\AppContractDataProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\BlockNumberProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\CachedOracleRateProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Cache\WpOracleRateCache;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpErc4361NonceRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpInvoiceTokenRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpNetworkCategoryRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpRefreshTokenRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpSellerRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpServerSignerRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Repository\WpInstallOriginUrl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Repository\WpPausedRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Repository\WpSctaUrlRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Repository\WpLogLevelRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpAccessTokenExpirationProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpAccessTokenCookieProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpCookieNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpAccessTokenRequestProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpErc4361NonceProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpErc4361PropertyProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpInvoiceTokenCookieProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpInvoiceTokenProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpJwtAlgorithmProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Repository\WpJwtSecretKeyRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpLockService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpPluginMigrationService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpPostTitleProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpPluginTeardownService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpRefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpRefreshTokenService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpSiteService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpUserAccessProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpSalesHistoryQueryService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpTransactionService;
use wpdb;

use function DI\autowire;
use function DI\get;

final class ContainerDefinitions {
	public static function getDefinitions(): array {
		return array(
			wpdb::class                          => fn() => $GLOBALS['wpdb'],

			// TableGateway
			// ChainTable::class => autowire(),

			// Repository
			AppContractRepository::class         => autowire( WpAppContractRepository::class ),
			ChainRepository::class               => autowire( WpChainRepository::class ),
			InvoiceRepository::class             => autowire( WpInvoiceRepository::class ),
			NetworkCategoryRepository::class     => autowire( WpNetworkCategoryRepository::class ),
			OracleRepository::class              => autowire( WpOracleRepository::class ),
			PostRepository::class                => autowire( WpPostRepository::class ),
			ServerSignerRepository::class        => autowire( WpServerSignerRepository::class ),
			TokenRepository::class               => autowire( WpTokenRepository::class ),
			SellerRepository::class              => autowire( WpSellerRepository::class ),
			RefreshTokenRepository::class        => autowire( WpRefreshTokenRepository::class ),
			InvoiceTokenRepository::class        => autowire( WpInvoiceTokenRepository::class ),
			Erc4361NonceRepository::class        => autowire( WpErc4361NonceRepository::class ),
			PausedRepository::class              => autowire( WpPausedRepository::class ),
			SctaUrlRepository::class             => autowire( WpSctaUrlRepository::class ),
			InstallOriginUrl::class              => autowire( WpInstallOriginUrl::class ),
			JwtSecretKeyRepository::class        => autowire( WpJwtSecretKeyRepository::class ),

			// Service
			PostTitleProvider::class             => autowire( WpPostTitleProvider::class ),
			RateProvider::class                  => get( CachedOracleRateProvider::class ),
			// CachedRateProvider::class    => autowire()->constructor(
			// get( RateTransient::class ),
			// get( OracleRateProviderImpl::class )
			// ),
			UserAccessProvider::class            => autowire( WpUserAccessProvider::class ),
			PaidContentService::class            => autowire( PaidContentServiceImpl::class ),
			AppContractDataProvider::class       => autowire( AppContractDataProviderImpl::class ),
			BlockNumberProvider::class           => autowire( BlockNumberProviderImpl::class ),
			TransactionService::class            => autowire( WpTransactionService::class ),
			LockService::class                   => autowire( WpLockService::class ),
			SalesHistoryQueryService::class      => autowire( WpSalesHistoryQueryService::class ),
			JwtAlgorithmProvider::class          => autowire( WpJwtAlgorithmProvider::class ),
			AccessTokenExpirationProvider::class => autowire( WpAccessTokenExpirationProvider::class ),
			AccessTokenRequestProvider::class    => autowire( WpAccessTokenRequestProvider::class ),
			CookieNameProvider::class            => autowire( WpCookieNameProvider::class ),
			AccessTokenCookieProvider::class     => autowire( WpAccessTokenCookieProvider::class ),
			RefreshTokenCookieProvider::class    => autowire( WpRefreshTokenCookieProvider::class ),
			InvoiceTokenCookieProvider::class    => autowire( WpInvoiceTokenCookieProvider::class ),
			RefreshTokenService::class           => autowire( WpRefreshTokenService::class ),
			InvoiceTokenProvider::class          => autowire( WpInvoiceTokenProvider::class ),
			Erc4361PropertyProvider::class       => autowire( WpErc4361PropertyProvider::class ),
			Erc4361NonceProvider::class          => autowire( WpErc4361NonceProvider::class ),
			SiteService::class                   => autowire( WpSiteService::class ),
			PluginMigrationService::class        => autowire( WpPluginMigrationService::class ),
			PluginTeardownService::class         => autowire( WpPluginTeardownService::class ),

			// Cache
			OracleRateCache::class               => autowire( WpOracleRateCache::class ),

			// Logging
			Logger::class                        => autowire( SimpleLogger::class ),
			LogLevelRepository::class            => autowire( WpLogLevelRepository::class ),
		);
	}
}
