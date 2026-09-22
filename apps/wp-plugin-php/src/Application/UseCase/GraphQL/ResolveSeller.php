<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Domain\Repository\SellerRepository;

class ResolveSeller {


	public function __construct( private readonly SellerRepository $seller_repository ) {}

	public function handle( array $root_value, array $args ) {
		$seller = $this->seller_repository->get();

		return array(
			'address'        => $seller?->address()->value(),
			'signingMessage' => $seller?->signingMessage()->value(),
			'signature'      => $seller?->signature()->hex()->value(),
		);
	}
}
