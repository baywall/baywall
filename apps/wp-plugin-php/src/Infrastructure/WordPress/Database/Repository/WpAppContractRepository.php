<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\AppContractTable;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\AppContractTableRecord;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\CrawledBlockTable;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\CrawledBlockTableRecord;

class WpAppContractRepository implements AppContractRepository {
	public function __construct( AppContractTable $app_contract_table, CrawledBlockTable $crawled_block_table, ChainRepository $chain_repository ) {
		$this->app_contract_table  = $app_contract_table;
		$this->crawled_block_table = $crawled_block_table;
		$this->chain_repository    = $chain_repository;
	}
	private AppContractTable $app_contract_table;
	private CrawledBlockTable $crawled_block_table;
	private ChainRepository $chain_repository;

	/** @inheritdoc */
	public function get( ChainId $chain_id ): ?AppContract {
		$records = $this->app_contract_table->all();
		$records = array_filter(
			$records,
			fn( $record ) => $record->chainIdValue() === $chain_id->value()
		);
		assert( count( $records ) <= 1, '[68E05B97] should return at most one record. - ' . count( $records ) );

		$crawled_block_number_records = $this->crawled_block_table->all();
		$crawled_block_number_records = array_filter(
			$crawled_block_number_records,
			fn( $record ) => $record->chainIdValue() === $chain_id->value()
		);
		assert( count( $crawled_block_number_records ) <= 1, '[C5AB3471] should return at most one record. - ' . count( $crawled_block_number_records ) );

		return empty( $records ) ? null : new AppContractImpl(
			$this->chain_repository->get( $chain_id ),
			array_values( $records )[0],
			empty( $crawled_block_number_records ) ? null : array_values( $crawled_block_number_records )[0]
		);
	}

	/** @inheritdoc */
	public function save( AppContract $app_contract ): void {
		// コントラクト情報はプラグインインストール時に設定され、以降変更されないため保存処理は不要
		// $this->app_contract_table->save( $app_contract );

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
	public function __construct( Chain $chain, AppContractTableRecord $record, ?CrawledBlockTableRecord $crawled_block_record ) {
		parent::__construct(
			$chain,
			Address::from( $record->addressValue() ),
			$crawled_block_record ? BlockNumber::fromInt( $crawled_block_record->blockNumberValue() ) : null,
			$crawled_block_record ? UnixTimestamp::fromMySQL( $crawled_block_record->updatedAtValue() ) : null
		);
	}
}
