<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\TransactionHash;

/**
 * ペイウォール解除時のトランザクションに関するデータを記録するテーブル
 * ※ トランザクションハッシュやブロック番号などの情報を保持
 */
class UnlockPaywallTransactionTable extends TableBase {
	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->unlockPaywallTransaction() );
	}

	public function save( InvoiceId $invoice_id, ChainId $chain_id, BlockNumber $block_number, TransactionHash $transaction_hash ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`invoice_id`, `chain_id`, `block_number`, `transaction_hash`)
			VALUES (%s, %d, %d, %s)
			ON DUPLICATE KEY UPDATE
				`chain_id` = VALUES(`chain_id`),
				`block_number` = VALUES(`block_number`),
				`transaction_hash` = VALUES(`transaction_hash`)
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_id->ulid(), $chain_id->value(), $block_number->int(), $transaction_hash->value() );

		$result = $this->wpdb()->query( $sql );
		assert( $result <= 1, "[C5EB0772] Failed to save unlock paywall transaction. {$result}" );
		if ( false === $result ) {
			throw new \RuntimeException( '[CA6349AD] Failed to save unlock paywall transaction. ' . $this->wpdb()->last_error );
		}
	}

	public function exists( InvoiceId $invoice_id ): bool {
		$sql = <<<SQL
			SELECT `invoice_id` FROM `{$this->tableName()}`
			WHERE `invoice_id` = %s
			LIMIT 1
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_id->ulid() );
		$row = $this->safeGetRow( $sql );

		return $row !== null;
	}
}
