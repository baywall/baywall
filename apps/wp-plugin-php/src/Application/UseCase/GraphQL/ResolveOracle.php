<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

class ResolveOracle {

	private OracleRepository $oracle_repository;

	public function __construct(
		OracleRepository $oracle_repository
	) {
		$this->oracle_repository = $oracle_repository;
	}

	public function handle( array $root_value, array $args ): array {
		$chain_id = ChainId::from( $args['chainId'] );
		$address  = Address::from( $args['address'] );

		$oracles = $this->oracle_repository->all();

		$filtered_oracles = ( new OraclesFilter() )
			->byChainId( $chain_id )
			->byAddress( $address )
			->apply( $oracles );

		// チェーンIDとアドレスを指定しているので $filtered_oracles の長さは必ず1
		if ( 1 !== count( $filtered_oracles ) ) {
			throw new \RuntimeException( "[83F9617E] oracle data is not found or duplicated. chain id: {$chain_id}, address: {$address}, count: " . count( $filtered_oracles ) );
		}
		$oracle = array_values( $filtered_oracles )[0];

		$chain_callback = fn() => $root_value['chain'](
			$root_value,
			array(
				'chainId' => $oracle->chain()->id()->value(),
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
