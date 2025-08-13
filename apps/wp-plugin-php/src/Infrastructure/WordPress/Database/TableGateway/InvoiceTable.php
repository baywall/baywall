<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\InvoiceTableRecord;

/**
 * 発行した請求書の情報を保存するテーブル
 */
class InvoiceTable extends TableBase {
	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->invoice() );
	}

	/**
	 *
	 * @param InvoiceId $invoice_id
	 * @return null|InvoiceTableRecord
	 */
	public function select( InvoiceId $invoice_id ) {
		$sql = <<<SQL
			SELECT
				`id`,
				`post_id`,
				`chain_id`,
				`selling_amount`,
				`selling_symbol`,
				`seller_address`,
				`payment_token_address`,
				`payment_amount`,
				`consumer_address`,
				`nonce`
			FROM `{$this->tableName()}`
			WHERE `id` = %s
		SQL;

		$sql = $this->prepare( $sql, $invoice_id->ulid() );

		$record = $this->safeGetRow( $sql );

		return $record === null ? null : new InvoiceTableRecord( $record );
	}

	public function save( Invoice $invoice ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
				( `id`, `post_id`, `chain_id`, `selling_amount`, `selling_symbol`, `seller_address`, `payment_token_address`, `payment_amount`, `consumer_address`, `nonce` )
			VALUES
				( %s, %d, %d, %s, %s, %s, %s, %s, %s, %s )
			ON DUPLICATE KEY UPDATE
				`post_id` = VALUES(`post_id`),
				`chain_id` = VALUES(`chain_id`),
				`selling_amount` = VALUES(`selling_amount`),
				`selling_symbol` = VALUES(`selling_symbol`),
				`seller_address` = VALUES(`seller_address`),
				`payment_token_address` = VALUES(`payment_token_address`),
				`payment_amount` = VALUES(`payment_amount`),
				`consumer_address` = VALUES(`consumer_address`),
				`nonce` = VALUES(`nonce`)
		SQL;

		$sql = $this->prepare(
			$sql,
			$invoice->id()->ulid(),
			$invoice->postId()->value(),
			$invoice->chainId()->value(),
			$invoice->sellingPrice()->amount()->value(),
			$invoice->sellingPrice()->symbol()->value(),
			$invoice->sellerAddress()->value(),
			$invoice->paymentTokenAddress()->value(),
			$invoice->paymentAmount()->value(),
			$invoice->consumerAddress()->value(),
			$invoice->nonce() ? $invoice->nonce()->value() : null
		);

		$this->safeQuery( $sql );
	}
}
