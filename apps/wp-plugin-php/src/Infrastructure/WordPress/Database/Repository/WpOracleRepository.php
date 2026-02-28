<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\OracleTable;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\OracleTableRecord;

class WpOracleRepository implements OracleRepository {

	public function __construct( OracleTable $oracle_table ) {
		$this->oracle_table = $oracle_table;
	}

	private OracleTable $oracle_table;

	/**
	 * Repositoryに存在するOracle一覧を取得します。
	 *
	 * @return Oracle[]
	 */
	public function all(): array {
		return array_map(
			fn( OracleTableRecord $record ) => new OracleImpl( $record ),
			$this->oracle_table->all()
		);
	}

	/** @inheritdoc */
	public function save( Oracle $oracle ): void {
		$this->oracle_table->save( $oracle );
	}
}

/** @internal */
class OracleImpl extends Oracle {
	public function __construct( OracleTableRecord $oracle_record ) {
		parent::__construct(
			ChainId::from( $oracle_record->chainIdValue() ),
			Address::from( $oracle_record->addressValue() ),
			SymbolPair::from(
				Symbol::from( $oracle_record->baseSymbolValue() ),
				Symbol::from( $oracle_record->quoteSymbolValue() )
			)
		);
	}
}
