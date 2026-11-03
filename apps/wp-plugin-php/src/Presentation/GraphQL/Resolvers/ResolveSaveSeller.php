<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\SaveSeller;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Signature;
use Baywall\Core\Domain\ValueObject\SigningMessage;

class ResolveSaveSeller implements Resolver {
	public function __construct(
		private readonly SaveSeller $save_seller
	) {}

	public function handle( array $root_value, array $args ): bool {
		return $this->save_seller->handle(
			Address::from( $args['address'] ),
			SigningMessage::from( $args['signingMessage'] ),
			Signature::from( $args['signature'] )
		);
	}
}
