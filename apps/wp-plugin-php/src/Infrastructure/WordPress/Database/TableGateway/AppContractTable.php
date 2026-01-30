<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\AppContractTableRecord;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use stdClass;

/**
 * Appコントラクトの情報を記録するテーブル
 * ※ `crawled_block_number`の初期化は invoice の発行時に行われます。
 */
class AppContractTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->appContract();
	}

	/**
	 * @return AppContractTableRecord[]
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `address`
			FROM `{$this->table_name}`
		SQL;
		$results = $this->wpdb->getResults( $sql );

		return array_map(
			fn( stdClass $record ) => new AppContractTableRecord( $record ),
			$results
		);
	}
}
