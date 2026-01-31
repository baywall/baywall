<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;

class ResolveSeller {

	private SellerRepository $seller_repository;

	public function __construct( SellerRepository $seller_repository ) {
		$this->seller_repository = $seller_repository;
	}

	public function handle( array $root_value, array $args ) {
		$seller = $this->seller_repository->get();

		return array(
			'address'        => $seller ? $seller->address()->value() : null,
			'signingMessage' => $seller ? $seller->signingMessage()->value() : null,
			'signature'      => $seller ? $seller->signature()->hex()->value() : null,
		);
	}
}
