<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Record;

use Baywall\Core\Infrastructure\WordPress\Database\Record\Base\RecordBase;
use stdClass;

class InvoiceTableRecord extends RecordBase {
	public function __construct( stdClass $record ) {
		$record->post_id                = (int) $record->post_id;
		$record->chain_id               = (int) $record->chain_id;
		$record->payment_token_decimals = (int) $record->payment_token_decimals;

		$this->import( $record );
	}

	protected string $invoice_id;
	protected int $post_id;
	protected int $chain_id;
	protected string $selling_amount;
	protected string $selling_symbol;
	protected string $seller_address;
	protected string $payment_token_address;
	protected string $payment_token_symbol;
	protected int $payment_token_decimals;
	protected string $payment_amount;
	protected string $buyer_address;

	public function invoiceIdValue(): string {
		return $this->invoice_id;
	}
	public function postIdValue(): int {
		return $this->post_id;
	}
	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function sellingAmountValue(): string {
		return $this->selling_amount;
	}
	public function sellingSymbolValue(): string {
		return $this->selling_symbol;
	}
	public function sellerAddressValue(): string {
		return $this->seller_address;
	}
	public function paymentTokenAddressValue(): string {
		return $this->payment_token_address;
	}
	public function paymentTokenSymbolValue(): string {
		return $this->payment_token_symbol;
	}
	public function paymentTokenDecimalsValue(): int {
		return $this->payment_token_decimals;
	}
	public function paymentAmountValue(): string {
		return $this->payment_amount;
	}
	public function buyerAddressValue(): string {
		return $this->buyer_address;
	}
}
