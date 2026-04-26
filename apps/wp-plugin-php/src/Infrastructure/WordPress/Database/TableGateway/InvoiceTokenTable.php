<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\InvoiceToken;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\InvoiceTokenTableRecord;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\WpInvoiceTokenHashString;

/**
 * 請求書トークンの情報を記録するテーブル
 */
class InvoiceTokenTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->invoiceToken();
	}

	public function get( WpInvoiceTokenHashString $wp_invoice_token_hash ): ?InvoiceTokenTableRecord {
		$sql = $this->wpdb->named_prepare(
			<<<SQL
				SELECT `invoice_token_hash`, `invoice_id`, `expires_at`, `revoked_at`
				FROM `{$this->table_name}`
				WHERE `invoice_token_hash` = :invoice_token_hash
				LIMIT 1
			SQL,
			array(
				':invoice_token_hash' => $wp_invoice_token_hash->value(),
			)
		);

		$record = $this->wpdb->get_row( $sql );

		return $record === null ? null : new InvoiceTokenTableRecord( $record );
	}

	public function add( InvoiceToken $invoice_token ): void {
		if ( $invoice_token->revokedAt() !== null ) {
			// 新規追加時、無効化日時はnull
			throw new \InvalidArgumentException( '[41E831F5] When adding a new invoice token, revoked_at must be null.' );
		}

		$result = $this->wpdb->insert(
			$this->table_name,
			array(
				'invoice_id'         => $invoice_token->invoiceId()->ulid(),
				'invoice_token_hash' => WpInvoiceTokenHashString::from( $invoice_token->token() )->value(),
				'expires_at'         => $invoice_token->expiresAt()->toMySqlValue(),
				'revoked_at'         => null, // 追加時はrevoked_atはNULLで登録
			)
		);

		if ( $result !== 1 ) {
			throw new \RuntimeException( '[B3EE1AE3] Failed to insert invoice token record.' );
		}
	}

	public function update( InvoiceToken $invoice_token ): void {
		$revoked_at_value         = $invoice_token->revokedAt() !== null
			? $invoice_token->revokedAt()->toMySqlValue()
			: null;
		$invoice_token_hash_value = WpInvoiceTokenHashString::from( $invoice_token->token() )->value();

		$result = $this->wpdb->update(
			$this->table_name,
			array(
				// ※ `expires_at`は更新しないこと
				'revoked_at' => $revoked_at_value,
			),
			array(
				'invoice_id'         => $invoice_token->invoiceId()->ulid(),
				'invoice_token_hash' => $invoice_token_hash_value,
			)
		);

		if ( $result !== 1 ) {
			throw new \RuntimeException( '[E53EA5FE] Failed to update invoice token record.' );
		}
	}

	/**
	 * 指定した日時よりも前に作成されたレコードを削除します
	 * ※ ただし、削除の対象となるのは「期限切れ」または「無効化された」レコードに限定
	 */
	public function deleteByCreatedAt( UnixTimestamp $target_time ): void {
		$current_time = UnixTimestamp::now();

		$this->wpdb->query(
			$this->wpdb->named_prepare(
				<<<SQL
					DELETE FROM `{$this->table_name}`
					WHERE `created_at` < :target_time
					AND ( `expires_at` < :current_time OR `revoked_at` IS NOT NULL )
				SQL,
				array(
					':target_time'  => $target_time->toMySqlValue(),
					':current_time' => $current_time->toMySqlValue(),
				)
			)
		);
	}
}
