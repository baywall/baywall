<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;
use Baywall\Core\Domain\ValueObject\UnlockPaywallTransferType;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;

/**
 * ペイウォール解除イベントのログ
 */
class UnlockPaywallTransferEventTable {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->unlockPaywallTransferEvent();
	}

	public function save( InvoiceId $invoice_id, ChainId $chain_id, TransactionHash $transaction_hash, int $log_index, Address $from, Address $to, Address $token_address, Amount $amount, UnlockPaywallTransferType $transfer_type ): void {
		// 数量に小数点が含まれることはない
		assert( $amount->decimals()->equals( Decimals::from( 0 ) ), '[F48CCCE8] Amount must be an integer.' );

		$result = $this->wpdb->insert(
			$this->table_name,
			array(
				'invoice_id'       => $invoice_id->ulid(),
				'chain_id'         => $chain_id->value(),
				'transaction_hash' => $transaction_hash->value(),
				'log_index'        => $log_index,
				'from_address'     => $from->value(),
				'to_address'       => $to->value(),
				'token_address'    => $token_address->value(),
				'amount'           => $amount->value(),
				'transfer_type'    => $transfer_type->value(),
				'created_at'       => UnixTimestamp::now()->value(),
			),
			array( '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d' )
		);
		assert( $result === 1, "[1C8FE9F7] Failed to save unlock paywall transfer event. {$result}" );
	}
}
