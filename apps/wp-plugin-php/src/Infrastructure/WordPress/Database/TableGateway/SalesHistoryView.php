<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Record\SalesHistoryViewRecord;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use stdClass;

/**
 * 販売情報を取得するためのクラス
 *
 * このクラスは、ペイウォール解除イベントテーブル等の複数のテーブルを結合して販売履歴を取得するために使用します
 */
class SalesHistoryView {

	private MyWpdb $wpdb;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb                = $wpdb;
		$this->tx_table_name       = $table_name_provider->unlockPaywallTransaction();
		$this->event_table_name    = $table_name_provider->unlockPaywallTransferEvent();
		$this->invoice_table_name  = $table_name_provider->invoice();
		$this->token_table_name    = $table_name_provider->token();
		$this->chain_table_name    = $table_name_provider->chain();
		$this->wp_posts_table_name = $wpdb->posts; // WordPressの投稿テーブル名を取得
	}
	/** トランザクション情報が格納されているテーブル名 */
	private string $tx_table_name;
	/** トークン転送イベント情報が格納されているテーブル名 */
	private string $event_table_name;
	/** インボイステーブル名 */
	private string $invoice_table_name;
	/** トークンテーブル名 */
	private string $token_table_name;
	/** WordPressの投稿テーブル名 */
	private string $wp_posts_table_name;
	/** チェーンテーブル名 */
	private string $chain_table_name;

	/**
	 *
	 * @return SalesHistoryViewRecord[]
	 */
	public function select( ?InvoiceId $filter_invoice_id ) {
		// ※ 時刻は invoice が作成された時刻を使用

		$sql = <<<SQL
			SELECT
				t1.invoice_id,
				t1.chain_id,
				t6.name AS chain_name,
				t1.block_number,
				t1.transaction_hash,
				-- t2_agg.token_address, => t3.payment_token_address
				-- t2_agg.total_amount, => t3.payment_amount
				-- t2_agg.customer_address, => t3.customer_address
				t2_agg.contract_address,
				t2_agg.contract_received_amount,
				-- t2_agg.seller_address, => t3.seller_address
				t2_agg.seller_received_amount,
				t2_agg.affiliate_address,
				t2_agg.affiliate_received_amount,
				UNIX_TIMESTAMP(t3.created_at) AS created_at_unix,
				t3.post_id,
				t3.selling_amount,
				t3.selling_symbol,
				t3.seller_address,
				t3.payment_token_address,
				t3.payment_amount,
				t3.customer_address,
				t4.symbol AS payment_token_symbol,
				t4.decimals AS payment_token_decimals,
				t5.post_title
			FROM
				{$this->tx_table_name} AS t1
			INNER JOIN (
				SELECT
					invoice_id,
					-- MAX(token_address) AS token_address, => t3.payment_token_address と同じ
					-- SUM(amount) AS total_amount, => t3.payment_amount と同じ
					-- MAX(from_address) AS customer_address, => t3.customer_address と同じ
					MAX(CASE WHEN transfer_type = 1 THEN to_address END) AS contract_address,
					MAX(CASE WHEN transfer_type = 1 THEN amount END) AS contract_received_amount,
					-- MAX(CASE WHEN transfer_type = 2 THEN to_address END) AS seller_address, => t3.seller_address と同じ
					MAX(CASE WHEN transfer_type = 2 THEN amount END) AS seller_received_amount,
					MAX(CASE WHEN transfer_type = 3 THEN to_address END) AS affiliate_address,
					MAX(CASE WHEN transfer_type = 3 THEN amount END) AS affiliate_received_amount
				FROM
					{$this->event_table_name}
				GROUP BY
					invoice_id
			) AS t2_agg
				ON t1.invoice_id = t2_agg.invoice_id
			INNER JOIN
				{$this->invoice_table_name} AS t3
				ON t1.invoice_id = t3.id
			LEFT JOIN
				{$this->token_table_name} AS t4
				ON t3.chain_id = t4.chain_id AND t3.payment_token_address = t4.address
			LEFT JOIN
				{$this->wp_posts_table_name} AS t5
				ON t3.post_id = t5.ID
			LEFT JOIN
				{$this->chain_table_name} AS t6
				ON t3.chain_id = t6.chain_id
		SQL;

		// 条件が指定されている場合はWHERE句を追加
		$where_conditions = array();
		if ( $filter_invoice_id !== null ) {
			$where_conditions[] = 't1.invoice_id = ' . $this->wpdb->prepare( ':invoice_id', array( ':invoice_id' => (string) $filter_invoice_id ) );
		}

		if ( ! empty( $where_conditions ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
		}

		$results = $this->wpdb->get_results( $sql );

		return array_map(
			static function ( stdClass $record ) {
				return new SalesHistoryViewRecord( $record );
			},
			$results
		);
	}
}
