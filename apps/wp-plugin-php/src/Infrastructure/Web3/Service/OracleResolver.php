<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\Specification\ChainsSpecification;
use Baywall\Core\Domain\Specification\OraclesSpecification;
use Baywall\Core\Domain\ValueObject\SymbolPair;

class OracleResolver {

	public function __construct(
		private readonly ChainRepository $chain_repository,
		private readonly OracleRepository $oracle_repository
	) {}

	/** `レート取得用のオラクル`を取得します。 */
	public function resolveRateOracle( SymbolPair $symbol_pair ): ?Oracle {
		// 接続可能性の判定用に、接続可能チェーンのID一覧を1回のI/Oで用意する
		$connectable_chain_ids = array_map(
			fn( $chain ) => $chain->id(),
			array_filter( $this->chain_repository->all(), ( new ChainsSpecification( is_connectable: true ) )->isSatisfiedBy( ... ) )
		);

		$specification       = new OraclesSpecification( symbol_pair: $symbol_pair, connectable_chain_ids: $connectable_chain_ids );
		$connectable_oracles = array_filter( $this->oracle_repository->all(), $specification->isSatisfiedBy( ... ) );

		if ( empty( $connectable_oracles ) ) {
			return null;    // 接続可能なOracleがない場合はnullを返す
		} else {
			// 接続可能なOracleが複数ある場合は、最初のものを使用(最初である必要は無いので、適宜変更可能)
			return array_values( $connectable_oracles )[0];
		}
	}
}
