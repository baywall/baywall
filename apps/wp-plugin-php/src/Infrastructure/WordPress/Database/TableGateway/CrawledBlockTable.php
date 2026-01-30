<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\CrawledBlockTableRecord;
use stdClass;

/**
 * Appコントラクトのクロール済みブロック番号を記録するテーブル
 * ※ `block_number`の初期化は invoice の発行時に行われます。
 */
class CrawledBlockTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->crawledBlock();
	}

	/**
	 * @return CrawledBlockTableRecord[]
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `block_number`, `updated_at`
			FROM `{$this->table_name}`
		SQL;
		$results = $this->wpdb->getResults( $sql );

		return array_map(
			fn( stdClass $record ) => new CrawledBlockTableRecord( $record ),
			$results
		);
	}

	public function save( ChainId $chain_id, BlockNumber $block_number ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
				(`chain_id`, `block_number`)
			VALUES
				(:chain_id, :block_number)
			ON DUPLICATE KEY UPDATE
				`block_number` = VALUES(`block_number`)
		SQL;
		$sql = $this->wpdb->prepare(
			$sql,
			array(
				':chain_id'     => $chain_id->value(),
				':block_number' => $block_number->int(),
			)
		);

		$this->wpdb->query( $sql );
	}
}
