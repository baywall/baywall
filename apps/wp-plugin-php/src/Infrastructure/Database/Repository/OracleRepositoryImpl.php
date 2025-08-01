<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\OracleTable;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\OracleTableRecord;

class OracleRepositoryImpl implements OracleRepository {

	public function __construct( OracleTable $oracle_table, ChainRepository $chain_repository ) {
		$this->oracle_table     = $oracle_table;
		$this->chain_repository = $chain_repository;
	}

	private OracleTable $oracle_table;
	private ChainRepository $chain_repository;

	/**
	 * Repositoryに存在するOracle一覧を取得します。
	 *
	 * @return Oracle[]
	 */
	public function all(): array {
		return array_map(
			function ( OracleTableRecord $record ) {
				$chain = $this->chain_repository->get( ChainID::from( $record->chainIdValue() ) );
				assert( $chain !== null, '[890CB3D8] Chain record not found for Oracle: ' . $record->chainIdValue() );
				return new OracleImpl( $record, $chain );
			},
			$this->oracle_table->all()
		);
	}
}

/** @internal */
class OracleImpl extends Oracle {
	public function __construct( OracleTableRecord $oracle_record, Chain $chain ) {
		parent::__construct(
			$chain,
			Address::from( $oracle_record->addressValue() ),
			SymbolPair::from(
				Symbol::from( $oracle_record->baseSymbolValue() ),
				Symbol::from( $oracle_record->quoteSymbolValue() )
			)
		);
	}
}
