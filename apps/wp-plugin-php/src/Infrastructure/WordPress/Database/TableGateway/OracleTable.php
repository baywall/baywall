<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\OracleTableRecord;
use stdClass;

/**
 * Oracleの情報を記録するテーブル
 */
class OracleTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->oracle();
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
			FROM `{$this->table_name}`
		SQL;

		$result = $this->wpdb->get_results( $sql );

		return array_map(
			fn( stdClass $record ) => new OracleTableRecord( $record ),
			$result
		);
	}

	public function save( Oracle $oracle ): void {
		// 一旦、ON DUPLICATE KEY UPDATEは不要なので使用しない。
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`chain_id`, `address`, `base_symbol`, `quote_symbol`)
			VALUES ( :chain_id, :address, :base_symbol, :quote_symbol )
		SQL;

		$sql = $this->wpdb->named_prepare(
			$sql,
			array(
				':chain_id'     => $oracle->chainId()->value(),
				':address'      => $oracle->address()->value(),
				':base_symbol'  => $oracle->symbolPair()->base()->value(),
				':quote_symbol' => $oracle->symbolPair()->quote()->value(),
			)
		);

		$this->wpdb->query( $sql );
	}
}
