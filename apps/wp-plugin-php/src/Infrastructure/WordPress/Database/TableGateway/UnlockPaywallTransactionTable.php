<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Baywall\Core\Domain\ValueObject\BlockHash;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\ValueObject\UnlockPaywallTransactionTableRecord;

/**
 * ペイウォール解除時のトランザクションに関するデータを記録するテーブル
 * ※ トランザクションハッシュやブロック番号などの情報を保持
 */
class UnlockPaywallTransactionTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->unlockPaywallTransaction();
	}

	public function save( InvoiceId $invoice_id, ChainId $chain_id, BlockNumber $block_number, UnixTimestamp $block_timestamp, TransactionHash $transaction_hash, BlockHash $block_hash, bool $removed ): void {
		$now = UnixTimestamp::now()->value();
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`invoice_id`, `chain_id`, `block_number`, `block_timestamp`, `transaction_hash`, `block_hash`, `removed`, `created_at`)
			VALUES (:invoice_id, :chain_id, :block_number, :block_timestamp, :transaction_hash, :block_hash, :removed, :created_at)
			ON DUPLICATE KEY UPDATE
				`chain_id` = VALUES(`chain_id`),
				`block_number` = VALUES(`block_number`),
				`block_timestamp` = VALUES(`block_timestamp`),
				`transaction_hash` = VALUES(`transaction_hash`),
				`block_hash` = VALUES(`block_hash`),
				`removed` = VALUES(`removed`)
		SQL;

		$sql = $this->wpdb->named_prepare(
			$sql,
			array(
				':invoice_id'       => $invoice_id->ulid(),
				':chain_id'         => $chain_id->value(),
				':block_number'     => $block_number->int(),
				':block_timestamp'  => $block_timestamp->value(),
				':transaction_hash' => $transaction_hash->value(),
				':block_hash'       => $block_hash->value(),
				':removed'          => $removed,
				':created_at'       => $now,
			)
		);

		$result = $this->wpdb->query( $sql );
		// ※ `INSERT ... ON DUPLICATE KEY UPDATE` の affected rows は 0: 変化なし / 1: 挿入 / 2: 更新のため、更新も許容する
		assert( $result <= 2, "[C5EB0772] Failed to save unlock paywall transaction. {$result}" );
	}

	/** 指定した請求書IDに対応するトランザクション情報を取得します */
	public function get( InvoiceId $invoice_id ): ?UnlockPaywallTransactionTableRecord {
		$sql = <<<SQL
			SELECT `invoice_id`, `chain_id`, `block_number`, `block_timestamp`, `transaction_hash` FROM `{$this->table_name}`
			WHERE `invoice_id` = :invoice_id
			ORDER BY `created_at` DESC, `block_number` DESC
			LIMIT 1
		SQL;

		$sql = $this->wpdb->named_prepare( $sql, array( ':invoice_id' => $invoice_id->ulid() ) );
		$row = $this->wpdb->get_row( $sql );

		return $row !== null ? new UnlockPaywallTransactionTableRecord( $row ) : null;
	}
}
