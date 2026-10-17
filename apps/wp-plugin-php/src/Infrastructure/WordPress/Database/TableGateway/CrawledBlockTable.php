<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Application\Service\Clock;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

/**
 * Appコントラクトのクロール済みブロック番号を記録するテーブル
 * ※ `block_number`の初期化は invoice の発行時に行われます。
 */
class CrawledBlockTable {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider, private readonly Clock $clock ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->crawledBlock();
	}

	/**
	 * クロール済みブロック番号を取得します
	 *
	 * 行が無い（未初期化）場合は null を返します
	 */
	public function get( ChainId $chain_id ): ?BlockNumber {
		$sql   = "SELECT `block_number` FROM `{$this->table_name}` WHERE `chain_id` = :chain_id";
		$sql   = $this->wpdb->named_prepare( $sql, array( ':chain_id' => $chain_id->value() ) );
		$value = $this->wpdb->get_var( $sql );

		// (int) キャストを null 判定より先に行うと、未初期化の null が BlockNumber::fromInt( 0 ) に化ける
		return null === $value ? null : BlockNumber::fromInt( (int) $value );
	}

	public function save( ChainId $chain_id, BlockNumber $block_number ): void {
		$now = $this->clock->now()->value();
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
