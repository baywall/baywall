<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\Repository\SearchCondition\SalesHistorySearchCondition;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\Record\SalesHistoryViewRecord;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
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
		$this->chain_table_name    = $table_name_provider->chain();
		$this->wp_posts_table_name = $wpdb->posts; // WordPressの投稿テーブル名を取得
	}
	/** トランザクション情報が格納されているテーブル名 */
	private string $tx_table_name;
	/** トークン転送イベント情報が格納されているテーブル名 */
	private string $event_table_name;
	/** インボイステーブル名 */
	private string $invoice_table_name;
	/** WordPressの投稿テーブル名 */
	private string $wp_posts_table_name;
	/** チェーンテーブル名 */
	private string $chain_table_name;

	/**
	 *
	 * @return SalesHistoryViewRecord[]
	 */
	public function select( SalesHistorySearchCondition $condition ) {
		// ※ 時刻はブロックタイムスタンプを使用

		$sql = <<<SQL
			SELECT
				t1.invoice_id,
				t1.chain_id,
				t5.name AS chain_name,
				t1.block_number,
				t1.transaction_hash,
				( SELECT MAX( e_contract.to_address ) FROM {$this->event_table_name} AS e_contract
					WHERE e_contract.chain_id = t1.chain_id AND e_contract.transaction_hash = t1.transaction_hash AND e_contract.invoice_id = t1.invoice_id AND e_contract.transfer_type = 1 ) AS contract_address,
				( SELECT MAX( e_contract_amount.amount ) FROM {$this->event_table_name} AS e_contract_amount
					WHERE e_contract_amount.chain_id = t1.chain_id AND e_contract_amount.transaction_hash = t1.transaction_hash AND e_contract_amount.invoice_id = t1.invoice_id AND e_contract_amount.transfer_type = 1 ) AS contract_received_amount,
				( SELECT MAX( e_seller_amount.amount ) FROM {$this->event_table_name} AS e_seller_amount
					WHERE e_seller_amount.chain_id = t1.chain_id AND e_seller_amount.transaction_hash = t1.transaction_hash AND e_seller_amount.invoice_id = t1.invoice_id AND e_seller_amount.transfer_type = 2 ) AS seller_received_amount,
				( SELECT MAX( e_affiliate.to_address ) FROM {$this->event_table_name} AS e_affiliate
					WHERE e_affiliate.chain_id = t1.chain_id AND e_affiliate.transaction_hash = t1.transaction_hash AND e_affiliate.invoice_id = t1.invoice_id AND e_affiliate.transfer_type = 3 ) AS affiliate_address,
				( SELECT MAX( e_affiliate_amount.amount ) FROM {$this->event_table_name} AS e_affiliate_amount
					WHERE e_affiliate_amount.chain_id = t1.chain_id AND e_affiliate_amount.transaction_hash = t1.transaction_hash AND e_affiliate_amount.invoice_id = t1.invoice_id AND e_affiliate_amount.transfer_type = 3 ) AS affiliate_received_amount,
				t1.block_timestamp AS block_timestamp,
				t3.post_id,
				t3.selling_amount,
				t3.selling_symbol,
				t3.seller_address,
				t3.payment_token_address,
				t3.payment_token_symbol,
				t3.payment_token_decimals,
				t3.payment_amount,
				t3.buyer_address,
				t4.post_title
			FROM
				{$this->tx_table_name} AS t1 FORCE INDEX FOR ORDER BY ( idx_{$this->tx_table_name}_B156B02C )
			INNER JOIN
				{$this->invoice_table_name} AS t3
				ON t1.invoice_id = t3.invoice_id
			LEFT JOIN
				{$this->wp_posts_table_name} AS t4
				ON t3.post_id = t4.ID
			LEFT JOIN
				{$this->chain_table_name} AS t5
				ON t3.chain_id = t5.chain_id
		SQL;

		$where_conditions = array();

		// 必ず適用する条件
		// イベント行が1行も無いトランザクションを返すと、SalesHistoryViewRecord の非 nullable プロパティへ NULL が入り TypeError になるため行集合を絞る
		// ※ EXISTS は MariaDB で semijoin (duplicate weedout) に書き換えられ、イベントテーブルを全件スキャンするため使わない
		$where_conditions[] = <<<SQL
			( SELECT COUNT(*) FROM {$this->event_table_name} AS e_exists
				WHERE e_exists.chain_id = t1.chain_id AND e_exists.transaction_hash = t1.transaction_hash AND e_exists.invoice_id = t1.invoice_id ) > 0
		SQL;

		// 条件が指定されている場合はWHERE句を追加
		// 請求書IDフィルタ
		$filter_invoice_id = $condition->invoiceId();
		if ( $filter_invoice_id !== null ) {
			$where_conditions[] = 't1.invoice_id = ' . $this->wpdb->named_prepare( ':invoice_id', array( ':invoice_id' => (string) $filter_invoice_id ) );
		}

		// チェーンIDフィルタ
		$filter_chain_id = $condition->chainId();
		if ( $filter_chain_id !== null ) {
			$where_conditions[] = 't1.chain_id = ' . $this->wpdb->named_prepare( ':chain_id', array( ':chain_id' => $filter_chain_id->value() ) );
		}

		// トランザクションハッシュフィルタ
		$filter_transaction_hash = $condition->transactionHash();
		if ( $filter_transaction_hash !== null ) {
			$where_conditions[] = 't1.transaction_hash = ' . $this->wpdb->named_prepare( ':transaction_hash', array( ':transaction_hash' => $filter_transaction_hash->value() ) );
		}

		// 日付範囲フィルタ（block_timestamp を使用）
		$date_from = $condition->dateFrom();
		if ( $date_from !== null ) {
			$where_conditions[] = 't1.block_timestamp >= ' . $this->wpdb->named_prepare( ':date_from', array( ':date_from' => $date_from ) );
		}
		$date_to = $condition->dateTo();
		if ( $date_to !== null ) {
			$where_conditions[] = 't1.block_timestamp <= ' . $this->wpdb->named_prepare( ':date_to', array( ':date_to' => $date_to ) );
		}

		$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );

		$sql .= ' ORDER BY t1.block_timestamp DESC';
		$sql .= ' LIMIT ' . $this->wpdb->named_prepare( ':limit', array( ':limit' => Config::SALES_HISTORIES_MAX_RESULTS ) );

		$results = $this->wpdb->get_results( $sql );

		return array_map(
			static function ( stdClass $record ) {
				return new SalesHistoryViewRecord( $record );
			},
			$results
		);
	}


	/**
	 * 販売履歴に指定した投稿IDと購入者アドレスが存在するかどうか(=購入済みかどうか)を返します
	 */
	public function existsByPostIdAndBuyerAddress( PostId $post_id, Address $buyer_address ): bool {
		$sql = <<<SQL
			SELECT
				COUNT(*) AS count
			FROM
				{$this->tx_table_name} AS t1
			INNER JOIN
				{$this->invoice_table_name} AS t2
				ON t1.invoice_id = t2.invoice_id
			WHERE
				t2.post_id = :post_id AND t2.buyer_address = :buyer_address
		SQL;

		$sql    = $this->wpdb->named_prepare(
			$sql,
			array(
				':post_id'       => $post_id->value(),
				':buyer_address' => $buyer_address->value(),
			)
		);
		$result = $this->wpdb->get_row( $sql );

		return isset( $result->count ) && (int) $result->count > 0;
	}
}
