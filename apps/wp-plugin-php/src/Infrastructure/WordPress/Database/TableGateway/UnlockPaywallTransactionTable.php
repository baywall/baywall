<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\TransactionHash;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\UnlockPaywallTransactionTableRecord;

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

	public function save( InvoiceId $invoice_id, ChainId $chain_id, BlockNumber $block_number, TransactionHash $transaction_hash ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`invoice_id`, `chain_id`, `block_number`, `transaction_hash`)
			VALUES (:invoice_id, :chain_id, :block_number, :transaction_hash)
			ON DUPLICATE KEY UPDATE
				`chain_id` = VALUES(`chain_id`),
				`block_number` = VALUES(`block_number`),
				`transaction_hash` = VALUES(`transaction_hash`)
		SQL;

		$sql = $this->wpdb->prepare(
			$sql,
			array(
				':invoice_id'       => $invoice_id->ulid(),
				':chain_id'         => $chain_id->value(),
				':block_number'     => $block_number->int(),
				':transaction_hash' => $transaction_hash->value(),
			)
		);

		$result = $this->wpdb->query( $sql );
		assert( $result <= 1, "[C5EB0772] Failed to save unlock paywall transaction. {$result}" );
	}

	/** 指定した請求書IDに対応するトランザクション情報を取得します */
	public function get( InvoiceId $invoice_id ): ?UnlockPaywallTransactionTableRecord {
		$sql = <<<SQL
			SELECT `invoice_id`, `chain_id`, `block_number`, `transaction_hash` FROM `{$this->table_name}`
			WHERE `invoice_id` = :invoice_id
			LIMIT 1
		SQL;

		$sql = $this->wpdb->prepare( $sql, array( ':invoice_id' => $invoice_id->ulid() ) );
		$row = $this->wpdb->get_row( $sql );

		return $row !== null ? new UnlockPaywallTransactionTableRecord( $row ) : null;
	}

	/** @deprecated */
	public function exists( InvoiceId $invoice_id ): bool {
		$sql = <<<SQL
			SELECT `invoice_id` FROM `{$this->table_name}`
			WHERE `invoice_id` = :invoice_id
			LIMIT 1
		SQL;

		$sql = $this->wpdb->prepare( $sql, array( ':invoice_id' => $invoice_id->ulid() ) );
		$row = $this->wpdb->get_row( $sql );

		return $row !== null;
	}
}
