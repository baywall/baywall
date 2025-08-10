<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;

class SellerResolver extends ResolverBase {

	public function __construct( SellerRepository $seller_repository ) {
		$this->seller_repository = $seller_repository;
	}
	private SellerRepository $seller_repository;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$seller = $this->seller_repository->get();

		$agreed_terms = null;
		if ( $seller !== null ) {
			$agreed_terms = array(
				'version'   => $seller->agreedTermsVersion()->value(),
				'message'   => $seller->signingMessage()->value(),  // TODO: プロパティ名を'signingMessage'に変更
				'signature' => $seller->signature()->value(),
			);
		}

		return array(
			'agreedTerms' => $agreed_terms,
		);
	}
}
