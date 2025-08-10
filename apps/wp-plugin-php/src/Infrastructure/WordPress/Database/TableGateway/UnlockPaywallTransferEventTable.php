<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Repository\Name\TableNameProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\UnlockPaywallTransferType;

/**
 * ペイウォール解除イベントのログ
 */
class UnlockPaywallTransferEventTable extends TableBase {
	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->unlockPaywallTransferEvent() );
	}

	public function save( InvoiceId $invoice_id, int $log_index, Address $from, Address $to, Address $token_address, Amount $amount, UnlockPaywallTransferType $transfer_type ): void {
		$result = $this->wpdb()->insert(
			$this->tableName(),
			array(
				'invoice_id'    => $invoice_id->ulid(),
				'log_index'     => $log_index,
				'from_address'  => $from->value(),
				'to_address'    => $to->value(),
				'token_address' => $token_address->value(),
				'amount'        => $amount->value(),
				'transfer_type' => $transfer_type->value(),
			)
		);
		assert( $result === 1, "[1C8FE9F7] Failed to save unlock paywall transfer event. {$result}" );
		if ( false === $result ) {
			throw new \RuntimeException( '[86C68ECA] Failed to save unlock paywall transfer event. ' . $this->wpdb()->last_error );
		}
	}
}
