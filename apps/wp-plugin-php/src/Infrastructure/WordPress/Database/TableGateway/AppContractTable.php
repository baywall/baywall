<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\AppContractTableRecord;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use stdClass;

/**
 * Appコントラクトの情報を記録するテーブル
 * ※ `crawled_block_number`の初期化は invoice の発行時に行われます。
 */
class AppContractTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->appContract() );
	}

	/**
	 * @return AppContractTableRecord[]
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `address`
			FROM `{$this->tableName()}`
		SQL;
		$results = $this->safeGetResults( $sql );

		return array_map(
			fn( stdClass $record ) => new AppContractTableRecord( $record ),
			$results
		);
	}
}
