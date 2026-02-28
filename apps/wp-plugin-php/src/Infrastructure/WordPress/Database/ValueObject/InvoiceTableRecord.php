<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class InvoiceTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->post_id  = (int) $record->post_id;
		$record->chain_id = (int) $record->chain_id;

		$this->import( $record );
	}

	protected string $id;
	protected int $post_id;
	protected int $chain_id;
	protected string $selling_amount;
	protected string $selling_symbol;
	protected string $seller_address;
	protected string $payment_token_address;
	protected string $payment_amount;
	protected string $customer_address;

	public function idValue(): string {
		return $this->id;
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
	public function paymentAmountValue(): string {
		return $this->payment_amount;
	}
	public function customerAddressValue(): string {
		return $this->customer_address;
	}
}
