<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\DI;

use Cornix\Serendipity\Core\Application\Service\AccessTokenExpirationProvider;
use Cornix\Serendipity\Core\Application\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Application\Service\InvoiceTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\JwtAlgorithmProvider;
use Cornix\Serendipity\Core\Application\Service\JwtSecretKeyProvider;
use Cornix\Serendipity\Core\Application\Service\PaidContentService;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\SalesHistoryService;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessProvider;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceTokenRepository;
use Cornix\Serendipity\Core\Domain\Repository\NetworkCategoryRepository;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\RefreshTokenRepository;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\InvoiceTokenService;
use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;
use Cornix\Serendipity\Core\Domain\Service\RateProvider;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
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
use Cornix\Serendipity\Core\Infrastructure\Logging\LogLevelProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\BlockNumberProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\CachedOracleRateProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\WalletServiceImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Cache\WpOracleRateCache;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpInvoiceTokenRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpNetworkCategoryRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpRefreshTokenRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpSellerRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpServerSignerRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Logging\WpLogLevelProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpAccessTokenExpirationProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpInvoiceTokenCookieProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpInvoiceTokenService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpJwtAlgorithmProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpJwtSecretKeyProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpPostTitleProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpRefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpRefreshTokenService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpUserAccessProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpSalesHistoryService;
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

			// Service
			WalletService::class                 => autowire( WalletServiceImpl::class ),
			PostTitleProvider::class             => autowire( WpPostTitleProvider::class ),
			RateProvider::class                  => get( CachedOracleRateProvider::class ),
			// CachedRateProvider::class    => autowire()->constructor(
			// get( RateTransient::class ),
			// get( OracleRateProviderImpl::class )
			// ),
			UserAccessProvider::class            => autowire( WpUserAccessProvider::class ),
			PaidContentService::class            => autowire( PaidContentServiceImpl::class ),
			BlockNumberProvider::class           => autowire( BlockNumberProviderImpl::class ),
			TransactionService::class            => autowire( WpTransactionService::class ),
			SalesHistoryService::class           => autowire( WpSalesHistoryService::class ),
			JwtAlgorithmProvider::class          => autowire( WpJwtAlgorithmProvider::class ),
			JwtSecretKeyProvider::class          => autowire( WpJwtSecretKeyProvider::class ),
			AccessTokenExpirationProvider::class => autowire( WpAccessTokenExpirationProvider::class ),
			RefreshTokenCookieProvider::class    => autowire( WpRefreshTokenCookieProvider::class ),
			InvoiceTokenCookieProvider::class    => autowire( WpInvoiceTokenCookieProvider::class ),
			RefreshTokenService::class           => autowire( WpRefreshTokenService::class ),
			InvoiceTokenService::class           => autowire( WpInvoiceTokenService::class ),

			// Cache
			OracleRateCache::class               => autowire( WpOracleRateCache::class ),

			// Logging
			Logger::class                        => autowire( SimpleLogger::class ),
			LogLevelProvider::class              => autowire( WpLogLevelProvider::class ),
		);
	}
}
