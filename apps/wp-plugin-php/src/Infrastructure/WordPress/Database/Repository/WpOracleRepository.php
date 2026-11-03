<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Symbol;
use Baywall\Core\Domain\ValueObject\SymbolPair;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\OracleTable;
use Baywall\Core\Infrastructure\WordPress\Database\Record\OracleTableRecord;

class WpOracleRepository implements OracleRepository {
	public function __construct( private readonly OracleTable $oracle_table ) {}

	/**
	 * Repositoryに存在するOracle一覧を取得します。
	 *
	 * @return Oracle[]
	 */
	public function all(): array {
		return array_map(
			fn( OracleTableRecord $record ) => new Oracle(
				ChainId::from( $record->chainIdValue() ),
				Address::from( $record->addressValue() ),
				SymbolPair::from(
					Symbol::from( $record->baseSymbolValue() ),
					Symbol::from( $record->quoteSymbolValue() )
				)
			),
			$this->oracle_table->all()
		);
	}

	/** @inheritdoc */
	public function save( Oracle $oracle ): void {
		$this->oracle_table->save( $oracle );
	}
}
