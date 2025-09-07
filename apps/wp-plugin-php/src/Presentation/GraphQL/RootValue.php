<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\GraphQL;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\UseCase\ResolveChain;
use Cornix\Serendipity\Core\Application\UseCase\ResolveNetworkCategories;
use Cornix\Serendipity\Core\Application\UseCase\ResolveNetworkCategory;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ChainsResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ConsumerTermsVersionResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\CurrentSellerTermsResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\GetErc20InfoResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\IssueInvoiceResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\PostResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SaveTokenResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\RequestPaidContentByNonceResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ResolverBase;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SalesHistoriesResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SaveChainResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SellerResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SellingContentResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SellingPriceResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ServerSignerResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SetSellerAgreedTermsResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\TokenResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\TokensResolver;
use DI\Container;

class RootValue {

	/**
	 * @param \DI\Container $container
	 * @return array<string, mixed>
	 */
	public function get( Container $container ) {

		/** @var array<string,ResolverBase|string> */
		$resolvers = array(
			// 非公開
			'chain'                     => ResolveChain::class,
			'networkCategory'           => ResolveNetworkCategory::class,
			'sellingContent'            => $container->get( SellingContentResolver::class ),
			'sellingPrice'              => $container->get( SellingPriceResolver::class ),
			'token'                     => $container->get( TokenResolver::class ),

			// Query
			'chains'                    => $container->get( ChainsResolver::class ),
			'consumerTermsVersion'      => $container->get( ConsumerTermsVersionResolver::class ),
			'currentSellerTerms'        => $container->get( CurrentSellerTermsResolver::class ),
			'networkCategories'         => ResolveNetworkCategories::class,
			'post'                      => $container->get( PostResolver::class ),
			'salesHistories'            => $container->get( SalesHistoriesResolver::class ),
			'seller'                    => $container->get( SellerResolver::class ),
			'serverSigner'              => $container->get( ServerSignerResolver::class ),
			'tokens'                    => $container->get( TokensResolver::class ),

			// Mutation
			'issueInvoice'              => $container->get( IssueInvoiceResolver::class ),
			'requestPaidContentByNonce' => $container->get( RequestPaidContentByNonceResolver::class ),
			'getErc20Info'              => $container->get( GetErc20InfoResolver::class ),
			'saveChain'                 => $container->get( SaveChainResolver::class ),
			'saveToken'                 => $container->get( SaveTokenResolver::class ),
			'setSellerAgreedTerms'      => $container->get( SetSellerAgreedTermsResolver::class ),
		);

		$result = array();
		foreach ( $resolvers as $field => $resolver ) {
			$result[ $field ] = function ( array $root_value, array $args ) use ( $resolver, $container ) {
				try {
					if ( is_string( $resolver ) ) {
						$resolver = $container->get( $resolver );
						return $resolver->handle( $root_value, $args );
					} else {
						// TODO: 削除
						return $resolver->resolve( $root_value, $args );
					}
				} catch ( \Throwable $e ) {
					$container->get( AppLogger::class )->error( $e );
					throw $e;
				}
			};
		}

		return $result;
	}
}
