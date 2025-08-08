<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;

class CrawlableChainsService {

	private ChainRepository $chain_repository;
	private AppContractRepository $app_contract_repository;

	public function __construct(
		ChainRepository $chain_repository,
		AppContractRepository $app_contract_repository
	) {
		$this->chain_repository        = $chain_repository;
		$this->app_contract_repository = $app_contract_repository;
	}

	/**
	 * Appコントラクトのクロール可能なチェーン一覧を取得します。
	 *
	 * @return Chain[] クロール可能なチェーンの配列
	 */
	public function getForAppContract() {
		/** @var Chain[] */
		$result = array();

		// 接続可能なチェーン一覧を取得
		$connectable_chains = ( new ChainsFilter() )
			->byConnectable()
			->apply( $this->chain_repository->all() );

		foreach ( $connectable_chains as $chain ) {
			$app_contract = $this->app_contract_repository->get( $chain->id() );
			// Appコントラクトのクロール済みブロック番号が記録されていないチェーンはクロール対象外
			if ( $app_contract === null || $app_contract->crawledBlockNumber() === null ) {
				continue;
			}

			// クロール対象のチェーンを追加
			$result[] = $chain;
		}

		return $result;
	}
}
