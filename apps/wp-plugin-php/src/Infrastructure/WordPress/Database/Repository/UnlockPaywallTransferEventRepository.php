<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\UnlockPaywallTransferEventTable;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Infrastructure\Web3\ValueObject\UnlockPaywallTransferEvent;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\UnlockPaywallTransactionTable;

class UnlockPaywallTransferEventRepository {

	public function __construct(
		private readonly UnlockPaywallTransactionTable $unlock_paywall_transaction_table,
		private readonly UnlockPaywallTransferEventTable $unlock_paywall_transfer_event_table
	) {}

	public function save( ChainId $chain_id, UnlockPaywallTransferEvent $event ) {
		// トランザクション情報を保存
		$this->unlock_paywall_transaction_table->save(
			$event->invoiceId(),
			$chain_id,
			$event->blockNumber(),
			$event->blockTimestamp(),
			$event->transactionHash(),
			$event->blockHash(),
			$event->removed()
		);

		// トークン転送インベント情報を保存
		$this->unlock_paywall_transfer_event_table->save(
			$event->invoiceId(),
			$chain_id,
			$event->transactionHash(),
			$event->logIndex(),
			$event->fromAddress(),
			$event->toAddress(),
			$event->tokenAddress(),
			$event->amount(),
			$event->transferType()
		);
	}
}
