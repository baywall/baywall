<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\GraphQL;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveChain;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveChains;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveConsumerTermsVersion;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveCurrentSellerTerms;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveIssueInvoice;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveNetworkCategories;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveNetworkCategory;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveOracle;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveOracles;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolvePost;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveRequestPaidContentByNonce;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveSalesHistories;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveSaveChain;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveSaveOracle;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveSaveToken;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveSeller;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveSellingContent;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveSellingPrice;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveServerSigner;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveSetSellerAgreedTerms;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveToken;
use Cornix\Serendipity\Core\Application\UseCase\GraphQL\ResolveTokens;
use Psr\Container\ContainerInterface;

class RootValue {

	private ContainerInterface $container;
	private AppLogger $logger;

	public function __construct( ContainerInterface $container, AppLogger $logger ) {
		$this->container = $container;
		$this->logger    = $logger;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get(): array {

		/** @var array<string,string> */
		$resolvers = array(
			// 非公開
			'chain'                     => ResolveChain::class,
			'oracle'                    => ResolveOracle::class,
			'networkCategory'           => ResolveNetworkCategory::class,
			'sellingContent'            => ResolveSellingContent::class,
			'sellingPrice'              => ResolveSellingPrice::class,
			'token'                     => ResolveToken::class,

			// Query
			'chains'                    => ResolveChains::class,
			'consumerTermsVersion'      => ResolveConsumerTermsVersion::class,
			'currentSellerTerms'        => ResolveCurrentSellerTerms::class,
			'networkCategories'         => ResolveNetworkCategories::class,
			'oracles'                   => ResolveOracles::class,
			'post'                      => ResolvePost::class,
			'salesHistories'            => ResolveSalesHistories::class,
			'seller'                    => ResolveSeller::class,
			'serverSigner'              => ResolveServerSigner::class,
			'tokens'                    => ResolveTokens::class,

			// Mutation
			'issueInvoice'              => ResolveIssueInvoice::class,
			'requestPaidContentByNonce' => ResolveRequestPaidContentByNonce::class,
			'saveChain'                 => ResolveSaveChain::class,
			'saveOracle'                => ResolveSaveOracle::class,
			'saveToken'                 => ResolveSaveToken::class,
			'setSellerAgreedTerms'      => ResolveSetSellerAgreedTerms::class,
		);

		$result = array();
		foreach ( $resolvers as $field => $resolver ) {
			$result[ $field ] = function ( array $root_value, array $args ) use ( $resolver ) {
				try {
					$resolver = $this->container->get( $resolver );
					return $resolver->handle( $root_value, $args );
				} catch ( \Throwable $e ) {
					$this->logger->error( $e );
					throw $e;
				}
			};
		}

		return $result;
	}
}
