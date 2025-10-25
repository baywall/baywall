<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\ServerSignerTableRecord;

/**
 * 署名用ウォレットテーブル
 */
class ServerSignerTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->serverSigner() );
	}

	public function get(): ?ServerSignerTableRecord {
		$sql = <<<SQL
			SELECT `address`, `base64_key`
			FROM `{$this->tableName()}`
		SQL;

		$results = $this->safeGetResults( $sql );
		if ( count( $results ) > 1 ) {
			// 2件以上データが存在することはない
			throw new \RuntimeException( '[81CCE569] More than one server signer data found.' );
		}

		// データが存在しない場合はnullを返す
		return count( $results ) === 0 ? null : new ServerSignerTableRecord( $results[0] );
	}
}
