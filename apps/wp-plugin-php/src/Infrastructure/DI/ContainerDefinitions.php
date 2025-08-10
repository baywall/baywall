<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\DI;

use Cornix\Serendipity\Core\Application\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Application\Service\PaidContentService;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessProvider;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;
use Cornix\Serendipity\Core\Domain\Service\RateProvider;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
use Cornix\Serendipity\Core\Infrastructure\Cache\OracleRateCache;
use Cornix\Serendipity\Core\Infrastructure\Content\PaidContentServiceImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\AppContractRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\ChainRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\InvoiceRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\OracleRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\PostRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\TokenRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Logging\Handler\SimpleLogger;
use Cornix\Serendipity\Core\Infrastructure\Logging\Logger;
use Cornix\Serendipity\Core\Infrastructure\Logging\LogLevelProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\BlockNumberProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\CachedOracleRateProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\WalletServiceImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Cache\WpOracleRateCache;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\SellerRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Logging\LogLevelProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PostTitleProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\UserAccessProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpTransactionService;
use wpdb;

use function DI\autowire;
use function DI\get;

final class ContainerDefinitions {
	public static function getDefinitions(): array {
		return array(
			wpdb::class                  => fn() => $GLOBALS['wpdb'],

			// TableGateway
			// ChainTable::class => autowire(),

			// Repository
			AppContractRepository::class => autowire( AppContractRepositoryImpl::class ),
			ChainRepository::class       => autowire( ChainRepositoryImpl::class ),
			InvoiceRepository::class     => autowire( InvoiceRepositoryImpl::class ),
			OracleRepository::class      => autowire( OracleRepositoryImpl::class ),
			PostRepository::class        => autowire( PostRepositoryImpl::class ),
			TokenRepository::class       => autowire( TokenRepositoryImpl::class ),
			SellerRepository::class      => autowire( SellerRepositoryImpl::class ),

			// Service
			WalletService::class         => autowire( WalletServiceImpl::class ),
			PostTitleProvider::class     => autowire( PostTitleProviderImpl::class ),
			RateProvider::class          => get( CachedOracleRateProvider::class ),
			// CachedRateProvider::class    => autowire()->constructor(
			// get( RateTransient::class ),
			// get( OracleRateProviderImpl::class )
			// ),
			UserAccessProvider::class    => autowire( UserAccessProviderImpl::class ),
			PaidContentService::class    => autowire( PaidContentServiceImpl::class ),
			BlockNumberProvider::class   => autowire( BlockNumberProviderImpl::class ),
			TransactionService::class    => autowire( WpTransactionService::class ),

			// Cache
			OracleRateCache::class       => autowire( WpOracleRateCache::class ),

			// Logging
			Logger::class                => autowire( SimpleLogger::class ),
			LogLevelProvider::class      => autowire( LogLevelProviderImpl::class ),
		);
	}
}
