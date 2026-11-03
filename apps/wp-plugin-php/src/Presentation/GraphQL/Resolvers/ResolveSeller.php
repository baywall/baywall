<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetSeller;

class ResolveSeller implements Resolver {
	public function __construct( private readonly GetSeller $get_seller ) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ) {
		$seller = $this->get_seller->handle();

		return array(
			'address'        => $seller?->address()->value(),
			'signingMessage' => $seller?->signingMessage()->value(),
			'signature'      => $seller?->signature()->hex()->value(),
		);
	}
}
