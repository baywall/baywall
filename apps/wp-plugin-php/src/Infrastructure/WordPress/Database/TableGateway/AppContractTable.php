<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\Record\AppContractTableRecord;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
use stdClass;

/**
 * Appコントラクトの情報を記録するテーブル
 */
class AppContractTable {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->appContract();
	}

	/**
	 * Appコントラクトの全行を取得します
	 *
	 * @return AppContractTableRecord[]
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `address`
			FROM `{$this->table_name}`
		SQL;
		$results = $this->wpdb->get_results( $sql );

		return array_map(
			static fn( stdClass $record ) => new AppContractTableRecord( $record ),
			$results
		);
	}
}
