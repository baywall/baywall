<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\GraphQL;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\UseCase\ResolveChain;
use Cornix\Serendipity\Core\Application\UseCase\ResolveChains;
use Cornix\Serendipity\Core\Application\UseCase\ResolveConsumerTermsVersion;
use Cornix\Serendipity\Core\Application\UseCase\ResolveCurrentSellerTerms;
use Cornix\Serendipity\Core\Application\UseCase\ResolveGetErc20Info;
use Cornix\Serendipity\Core\Application\UseCase\ResolveIssueInvoice;
use Cornix\Serendipity\Core\Application\UseCase\ResolveNetworkCategories;
use Cornix\Serendipity\Core\Application\UseCase\ResolveNetworkCategory;
use Cornix\Serendipity\Core\Application\UseCase\ResolvePost;
use Cornix\Serendipity\Core\Application\UseCase\ResolveRequestPaidContentByNonce;
use Cornix\Serendipity\Core\Application\UseCase\ResolveSalesHistories;
use Cornix\Serendipity\Core\Application\UseCase\ResolveSaveChain;
use Cornix\Serendipity\Core\Application\UseCase\ResolveSeller;
use Cornix\Serendipity\Core\Application\UseCase\ResolveSellingContent;
use Cornix\Serendipity\Core\Application\UseCase\ResolveSellingPrice;
use Cornix\Serendipity\Core\Application\UseCase\ResolveServerSigner;
use Cornix\Serendipity\Core\Application\UseCase\ResolveToken;
use Cornix\Serendipity\Core\Application\UseCase\ResolveTokens;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SaveTokenResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ResolverBase;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SetSellerAgreedTermsResolver;
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
			'sellingContent'            => ResolveSellingContent::class,
			'sellingPrice'              => ResolveSellingPrice::class,
			'token'                     => ResolveToken::class,

			// Query
			'chains'                    => ResolveChains::class,
			'consumerTermsVersion'      => ResolveConsumerTermsVersion::class,
			'currentSellerTerms'        => ResolveCurrentSellerTerms::class,
			'networkCategories'         => ResolveNetworkCategories::class,
			'post'                      => ResolvePost::class,
			'salesHistories'            => ResolveSalesHistories::class,
			'seller'                    => ResolveSeller::class,
			'serverSigner'              => ResolveServerSigner::class,
			'tokens'                    => ResolveTokens::class,

			// Mutation
			'issueInvoice'              => ResolveIssueInvoice::class,
			'requestPaidContentByNonce' => ResolveRequestPaidContentByNonce::class,
			'getErc20Info'              => ResolveGetErc20Info::class,
			'saveChain'                 => ResolveSaveChain::class,
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
