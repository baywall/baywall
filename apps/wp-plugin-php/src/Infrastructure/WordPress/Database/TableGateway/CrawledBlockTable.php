<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

/**
 * Appコントラクトのクロール済みブロック番号を記録するテーブル
 * ※ `block_number`の初期化は invoice の発行時に行われます。
 */
class CrawledBlockTable {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->crawledBlock();
	}

	public function save( ChainId $chain_id, BlockNumber $block_number ): void {
		$now = UnixTimestamp::now()->value();
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
				(`chain_id`, `block_number`, `created_at`, `updated_at`)
			VALUES
				(:chain_id, :block_number, :created_at, :updated_at)
			ON DUPLICATE KEY UPDATE
				`block_number` = VALUES(`block_number`),
				`updated_at` = VALUES(`updated_at`)
		SQL;
		$sql = $this->wpdb->named_prepare(
			$sql,
			array(
				':chain_id'     => $chain_id->value(),
				':block_number' => $block_number->int(),
				':created_at'   => $now,
				':updated_at'   => $now,
			)
		);

		$this->wpdb->query( $sql );
	}
}
