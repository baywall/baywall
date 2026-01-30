<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\ServerSignerTableRecord;

/**
 * 署名用ウォレットテーブル
 */
class ServerSignerTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->serverSigner();
	}

	public function get(): ?ServerSignerTableRecord {
		$sql = <<<SQL
			SELECT `address`, `base64_key`
			FROM `{$this->table_name}`
		SQL;

		$results = $this->wpdb->getResults( $sql );
		if ( count( $results ) > 1 ) {
			// 2件以上データが存在することはない
			throw new \RuntimeException( '[81CCE569] More than one server signer data found.' );
		}

		// データが存在しない場合はnullを返す
		return count( $results ) === 0 ? null : new ServerSignerTableRecord( $results[0] );
	}
}
