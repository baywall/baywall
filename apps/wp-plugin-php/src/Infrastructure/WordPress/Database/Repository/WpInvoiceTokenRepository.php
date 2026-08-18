<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Domain\Entity\InvoiceToken;
use Baywall\Core\Domain\Repository\InvoiceTokenRepository;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\InvoiceTokenString;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\InvoiceTokenTable;
use Baywall\Core\Infrastructure\WordPress\ValueObject\WpInvoiceTokenHashString;

class WpInvoiceTokenRepository implements InvoiceTokenRepository {

	private InvoiceTokenTable $invoice_token_table;

	public function __construct( InvoiceTokenTable $invoice_token_table ) {
		$this->invoice_token_table = $invoice_token_table;
	}

	/** 指定した請求書トークン文字列に合致する請求書情報を取得します。 */
	public function get( InvoiceTokenString $invoice_token_string ): ?InvoiceToken {
		$record = $this->invoice_token_table->get( WpInvoiceTokenHashString::from( $invoice_token_string ) );

		return $record !== null ? InvoiceToken::create(
			InvoiceId::fromUlidValue( $record->invoiceIdValue() ),
			$invoice_token_string,
			UnixTimestamp::fromMySql( $record->expiresAtValue() ),
			UnixTimestamp::fromMySqlNullable( $record->revokedAtValue() )
		) : null;
	}

	/** 請求書トークン情報を追加します。 */
	public function add( InvoiceToken $invoice_token ): void {
		$this->invoice_token_table->add( $invoice_token );
	}

	/** 請求書トークン情報を更新します。 */
	public function update( InvoiceToken $invoice_token ): void {
		$this->invoice_token_table->update( $invoice_token );
	}

	/** @inheritdoc */
	public function deleteByCreatedAt( UnixTimestamp $target_time ): void {
		$this->invoice_token_table->deleteByCreatedAt( $target_time );
	}
}
