<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\OracleTableRecord;
use stdClass;

/**
 * Oracleの情報を記録するテーブル
 */
class OracleTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->oracle() );
	}

	/**
	 * Oracleデータ一覧を取得します。
	 *
	 * @return OracleTableRecord[]
	 */
	public function all(): array {
		// Oracleのデータ量は少ないので絞り込みは上位で行う
		$sql = <<<SQL
			SELECT `chain_id`, `address`, `base_symbol`, `quote_symbol`
			FROM `{$this->tableName()}`
		SQL;

		$result = $this->safeGetResults( $sql );

		return array_map(
			fn( stdClass $record ) => new OracleTableRecord( $record ),
			$result
		);
	}
}
