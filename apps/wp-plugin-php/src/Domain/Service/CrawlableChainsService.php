<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Repository\AppContractRepository;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\CrawledBlockRepository;
use Baywall\Core\Domain\Specification\ChainsSpecification;

class CrawlableChainsService {


	public function __construct(
		private readonly ChainRepository $chain_repository,
		private readonly AppContractRepository $app_contract_repository,
		private readonly CrawledBlockRepository $crawled_block_repository
	) {}

	/**
	 * Appコントラクトのクロール可能なチェーン一覧を取得します。
	 *
	 * @return Chain[] クロール可能なチェーンの配列
	 */
	public function getForAppContract() {
		/** @var Chain[] */
		$result = array();

		// 接続可能なチェーン一覧を取得
		$connectable_chains = array_filter(
			$this->chain_repository->all(),
			( new ChainsSpecification( is_connectable: true ) )->isSatisfiedBy( ... )
		);

		foreach ( $connectable_chains as $chain ) {
			// Appコントラクトが未登録のチェーン、又はクロール済みブロック番号が未初期化のチェーンはクロール対象外
			if ( null === $this->app_contract_repository->get( $chain->id() ) ) {
				continue;
			}
			if ( null === $this->crawled_block_repository->get( $chain->id() ) ) {
				continue;
			}

			// クロール対象のチェーンを追加
			$result[] = $chain;
		}

		return $result;
	}
}
