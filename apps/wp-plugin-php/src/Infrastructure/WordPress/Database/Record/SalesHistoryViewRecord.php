<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Record;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Record\Base\RecordBase;
use stdClass;

class SalesHistoryViewRecord extends RecordBase {

	public function __construct( stdClass $record ) {
		$record->chain_id               = (int) $record->chain_id;
		$record->block_number           = (int) $record->block_number;
		$record->created_at_unix        = (int) $record->created_at_unix;
		$record->post_id                = (int) $record->post_id;
		$record->payment_token_decimals = (int) $record->payment_token_decimals;

		$this->import( $record );
	}

	public string $invoice_id;
	public int $chain_id;
	public string $chain_name;
	public int $block_number;
	public string $transaction_hash;
	public string $contract_address;
	public string $contract_received_amount;
	public string $seller_received_amount;
	public ?string $affiliate_address;
	public ?string $affiliate_received_amount;
	public int $created_at_unix;
	public int $post_id;
	public string $selling_amount;
	public string $selling_symbol;
	public string $seller_address;
	public string $payment_token_address;
	public string $payment_amount;
	public string $customer_address;
	public string $payment_token_symbol;
	public int $payment_token_decimals;
	public ?string $post_title;
}
