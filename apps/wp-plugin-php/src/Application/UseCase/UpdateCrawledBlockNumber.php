<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

/**
 * ペイウォール解除イベントのクロール済みブロック番号を更新します
 */
class UpdateUnlockPaywallEventCrawledBlockNumber {
	public function __construct( AppContractRepository $app_contract_repository ) {
		$this->app_contract_repository = $app_contract_repository;
	}

	private AppContractRepository $app_contract_repository;

	public function handle( ChainId $chain_id, BlockNumber $crawled_block_number ): void {
		$app_contract = $this->app_contract_repository->get( $chain_id );

		// 現在のブロック番号をクロール済みとして更新する
		$app_contract->setCrawledBlockNumber( $crawled_block_number );

		// 更新されたクロール済みブロック番号を保存
		$this->app_contract_repository->save( $app_contract );
	}
}
