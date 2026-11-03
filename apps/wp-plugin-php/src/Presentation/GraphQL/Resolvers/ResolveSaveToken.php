<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\SaveToken;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

/**
 * トークンの情報をサーバーに登録します。
 */
class ResolveSaveToken implements Resolver {
	public function __construct(
		private readonly SaveToken $save_token
	) {}

	/** @return bool */
	public function handle( array $root_value, array $args ) {
		/** @var bool */
		$is_payable = $args['isPayable'];

		return $this->save_token->handle(
			ChainId::from( $args['chainId'] ),
			Address::from( $args['address'] ),
			$is_payable
		);
	}
}
