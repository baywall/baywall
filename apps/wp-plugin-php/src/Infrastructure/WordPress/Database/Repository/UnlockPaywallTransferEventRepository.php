<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

class UnlockPaywallTransferEventRepository {

	public function __construct( UnlockPaywallTransferEventTable $unlock_paywall_transfer_event_table ) {
		$this->unlock_paywall_transfer_event_table = $unlock_paywall_transfer_event_table;
	}
	private UnlockPaywallTransferEventTable $unlock_paywall_transfer_event_table;

	public function save( InvoiceId $invoice_id, int $log_index, Address $from, Address $to, Address $token_address, Amount $amount, int $transfer_type ): void {
		$this->unlock_paywall_transfer_event_table->save( $invoice_id, $log_index, $from, $to, $token_address, $amount, $transfer_type );
	}
}
