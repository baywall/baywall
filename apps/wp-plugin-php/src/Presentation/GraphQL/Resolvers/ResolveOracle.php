<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\Specification\OraclesSpecification;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveOracle {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly OracleRepository $oracle_repository
	) {}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );
		$address  = Address::from( $args['address'] );

		$specification = new OraclesSpecification( chain_id: $chain_id, address: $address );

		$filtered_oracles = array_filter( $this->oracle_repository->all(), $specification->isSatisfiedBy( ... ) );

		// チェーンIDとアドレスを指定しているので $filtered_oracles の長さは必ず1
		if ( 1 !== count( $filtered_oracles ) ) {
			throw new \RuntimeException( "[83F9617E] oracle data is not found or duplicated. chain id: {$chain_id}, address: {$address}, count: " . count( $filtered_oracles ) );
		}
		$oracle = array_values( $filtered_oracles )[0];

		$chain_callback = fn() => $root_value['chain'](
			$root_value,
			array(
				'chainId' => $oracle->chainId()->value(),
			)
		);

		return array(
			'chain'       => $chain_callback,
			'address'     => $oracle->address()->value(),
			'baseSymbol'  => $oracle->symbolPair()->base()->value(),
			'quoteSymbol' => $oracle->symbolPair()->quote()->value(),
		);
	}
}
