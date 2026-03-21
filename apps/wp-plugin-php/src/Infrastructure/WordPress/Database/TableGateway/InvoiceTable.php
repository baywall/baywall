<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Repository\SearchCondition\InvoiceSearchCondition;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\InvoiceTableRecord;

/**
 * 発行した請求書の情報を保存するテーブル
 */
class InvoiceTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->invoice();
	}

	/**
	 *
	 * @param InvoiceSearchCondition $condition 検索条件
	 * @return InvoiceTableRecord[]
	 */
	public function select( InvoiceSearchCondition $condition ): array {
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
				`customer_address`
			FROM `{$this->table_name}`
		SQL;

		$where_clauses = array();
		if ( ! is_null( $condition->invoiceId() ) ) {
			$where_clauses[] = $this->wpdb->prepare( 'id = :invoice_id', array( ':invoice_id' => $condition->invoiceId()->ulid() ) );
		}
		if ( ! is_null( $condition->postId() ) ) {
			$where_clauses[] = $this->wpdb->prepare( 'post_id = :post_id', array( ':post_id' => $condition->postId()->value() ) );
		}
		if ( ! is_null( $condition->customerAddress() ) ) {
			$where_clauses[] = $this->wpdb->prepare( 'customer_address = :customer_address', array( ':customer_address' => $condition->customerAddress()->value() ) );
		}

		$sql     = $where_clauses ? $sql . ' WHERE ' . implode( ' AND ', $where_clauses ) : $sql;
		$records = $this->wpdb->get_results( $sql );

		return array_values( array_map( fn( \stdClass $record ) => new InvoiceTableRecord( $record ), $records ) );
	}

	public function save( Invoice $invoice ): void {
		// 支払い数量に小数点が含まれることはない
		assert( $invoice->paymentAmount()->decimals()->equals( Decimals::from( 0 ) ), '[65AA4D6E] Payment amount must be an integer.' );

		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
				( `id`, `post_id`, `chain_id`, `selling_amount`, `selling_symbol`, `seller_address`, `payment_token_address`, `payment_amount`, `customer_address` )
			VALUES
				( :invoice_id, :post_id, :chain_id, :selling_amount, :selling_symbol, :seller_address, :payment_token_address, :payment_amount, :customer_address )
			ON DUPLICATE KEY UPDATE
				`post_id` = VALUES(`post_id`),
				`chain_id` = VALUES(`chain_id`),
				`selling_amount` = VALUES(`selling_amount`),
				`selling_symbol` = VALUES(`selling_symbol`),
				`seller_address` = VALUES(`seller_address`),
				`payment_token_address` = VALUES(`payment_token_address`),
				`payment_amount` = VALUES(`payment_amount`),
				`customer_address` = VALUES(`customer_address`)
		SQL;

		$sql = $this->wpdb->prepare(
			$sql,
			array(
				':invoice_id'            => $invoice->id()->ulid(),
				':post_id'               => $invoice->postId()->value(),
				':chain_id'              => $invoice->chainId()->value(),
				':selling_amount'        => $invoice->sellingPrice()->amount()->value(),
				':selling_symbol'        => $invoice->sellingPrice()->symbol()->value(),
				':seller_address'        => $invoice->sellerAddress()->value(),
				':payment_token_address' => $invoice->paymentTokenAddress()->value(),
				':payment_amount'        => $invoice->paymentAmount()->value(),
				':customer_address'      => $invoice->customerAddress()->value(),
			)
		);

		$this->wpdb->query( $sql );
	}
}
