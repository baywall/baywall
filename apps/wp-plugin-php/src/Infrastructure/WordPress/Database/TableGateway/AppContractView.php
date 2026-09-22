<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\Record\AppContractViewRecord;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
use stdClass;

/**
 * Appコントラクトの情報を、チェーンごとのクロール済みブロック番号と結合して取得するためのクラス
 */
class AppContractView {

	private readonly MyWpdb $wpdb;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb                     = $wpdb;
		$this->app_contract_table_name  = $table_name_provider->appContract();
		$this->crawled_block_table_name = $table_name_provider->crawledBlock();
	}

	/** Appコントラクトの情報を記録するテーブル名 */
	private readonly string $app_contract_table_name;
	/** クロール済みのブロックを記録するテーブル名 */
	private readonly string $crawled_block_table_name;

	/**
	 * Appコントラクトの全行を、クロール済みブロック番号と結合して取得します
	 *
	 * クロール済みブロックの行が無いチェーンでも行を返すため、`block_number` / `updated_at` は `null` になり得ます
	 *
	 * @return AppContractViewRecord[]
	 */
	public function all(): array {
		$sql = <<<SQL
			SELECT
				t1.chain_id,
				t1.address,
				t2.block_number,
				t2.updated_at
			FROM
				{$this->app_contract_table_name} AS t1
			LEFT JOIN
				{$this->crawled_block_table_name} AS t2
					ON t1.chain_id = t2.chain_id
		SQL;

		$results = $this->wpdb->get_results( $sql );

		return array_map(
			static fn( stdClass $record ) => new AppContractViewRecord( $record ),
			$results
		);
	}
}
