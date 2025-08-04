<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\UnlockPaywallTransactionTable;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\TransactionHash;

class UnlockPaywallTransactionRepository {

	public function __construct( UnlockPaywallTransactionTable $unlock_paywall_transaction_table ) {
		$this->unlock_paywall_transaction_table = $unlock_paywall_transaction_table;
	}

	private UnlockPaywallTransactionTable $unlock_paywall_transaction_table;

	public function save( InvoiceId $invoice_id, ChainId $chain_id, BlockNumber $block_number, TransactionHash $transaction_hash ): void {
		$this->unlock_paywall_transaction_table->save( $invoice_id, $chain_id, $block_number, $transaction_hash );
	}
}
