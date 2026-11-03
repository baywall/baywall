<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetSellingPrice;
use Baywall\Core\Domain\ValueObject\PostId;

class ResolveSellingPrice implements Resolver {
	public function __construct(
		private readonly GetSellingPrice $get_selling_price
	) {}

	/** @return array<string, mixed>|null */
	public function handle( array $root_value, array $args ) {
		$price = $this->get_selling_price->handle( PostId::from( $args['postId'] ) );

		return $price === null ? null : array(
			'amount' => $price->amount()->value(),
			'symbol' => $price->symbol()->value(),
		);
	}
}
