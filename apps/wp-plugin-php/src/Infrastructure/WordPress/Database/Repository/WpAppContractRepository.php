<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Domain\Entity\AppContract;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Repository\AppContractRepository;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\Record\AppContractViewRecord;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\AppContractView;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\CrawledBlockTable;

class WpAppContractRepository implements AppContractRepository {
	public function __construct( AppContractView $app_contract_view, CrawledBlockTable $crawled_block_table, ChainRepository $chain_repository ) {
		$this->app_contract_view   = $app_contract_view;
		$this->crawled_block_table = $crawled_block_table;
		$this->chain_repository    = $chain_repository;
	}
	private AppContractView $app_contract_view;
	private CrawledBlockTable $crawled_block_table;
	private ChainRepository $chain_repository;

	/** @inheritdoc */
	public function get( ChainId $chain_id ): ?AppContract {
		$records = $this->app_contract_view->all();
		$records = array_filter(
			$records,
			fn( $record ) => $record->chain_id === $chain_id->value()
		);
		assert( count( $records ) <= 1, '[68E05B97] should return at most one record. - ' . count( $records ) );

		return empty( $records ) ? null : new AppContractImpl(
			$this->chain_repository->get( $chain_id ),
			array_values( $records )[0]
		);
	}

	/** @inheritdoc */
	public function save( AppContract $app_contract ): void {
		// コントラクト情報はプラグインインストール時に設定され、以降変更されないため保存処理は不要

		// クロール済みブロック番号の更新
		if ( $app_contract->crawledBlockNumber() !== null ) {
			$this->crawled_block_table->save(
				$app_contract->chain()->id(),
				$app_contract->crawledBlockNumber()
			);
		}
	}
}

/** @internal */
class AppContractImpl extends AppContract {
	public function __construct( Chain $chain, AppContractViewRecord $record ) {
		parent::__construct(
			$chain,
			Address::from( $record->address ),
			BlockNumber::fromIntNullable( $record->block_number ),
			$record->updated_at === null ? null : UnixTimestamp::from( $record->updated_at )
		);
	}
}
