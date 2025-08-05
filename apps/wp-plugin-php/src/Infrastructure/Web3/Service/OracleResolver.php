<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;

class OracleResolver {
	public function __construct( OracleRepository $oracle_repository ) {
		$this->oracle_repository = $oracle_repository;
	}
	private OracleRepository $oracle_repository;

	/** `レート取得用のオラクル`を取得します。 */
	public function resolveRateOracle( SymbolPair $symbol_pair ): ?Oracle {
		$connectable_oracles = ( new OraclesFilter() )
			->bySymbolPair( $symbol_pair )
			->byConnectable()
			->apply( $this->oracle_repository->all() );

		if ( empty( $connectable_oracles ) ) {
			return null;    // 接続可能なOracleがない場合はnullを返す
		} else {
			// 接続可能なOracleが複数ある場合は、最初のものを使用(最初である必要は無いので、適宜変更可能)
			return array_values( $connectable_oracles )[0];
		}
	}
}
