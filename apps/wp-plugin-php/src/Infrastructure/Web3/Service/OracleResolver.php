<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\Specification\OraclesFilter;
use Baywall\Core\Domain\ValueObject\SymbolPair;

class OracleResolver {

	public function __construct(
		private readonly ChainRepository $chain_repository,
		private readonly OracleRepository $oracle_repository
	) {}

	/** `レート取得用のオラクル`を取得します。 */
	public function resolveRateOracle( SymbolPair $symbol_pair ): ?Oracle {
		$connectable_oracles = ( new OraclesFilter( $this->chain_repository ) )
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
