<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject\UnlockPaywallTransferEvent;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\UnlockPaywallTransactionTable;

class UnlockPaywallTransferEventRepository {
	private UnlockPaywallTransactionTable $unlock_paywall_transaction_table;
	private UnlockPaywallTransferEventTable $unlock_paywall_transfer_event_table;

	public function __construct( UnlockPaywallTransactionTable $unlock_paywall_transaction_table, UnlockPaywallTransferEventTable $unlock_paywall_transfer_event_table ) {
		$this->unlock_paywall_transaction_table    = $unlock_paywall_transaction_table;
		$this->unlock_paywall_transfer_event_table = $unlock_paywall_transfer_event_table;
	}

	public function save( ChainId $chain_id, UnlockPaywallTransferEvent $event ) {
		// トランザクション情報を保存
		$this->unlock_paywall_transaction_table->save(
			$event->invoiceId(),
			$chain_id,
			$event->blockNumber(),
			$event->transactionHash()
		);

		// トークン転送インベント情報を保存
		$this->unlock_paywall_transfer_event_table->save(
			$event->invoiceId(),
			$event->logIndex(),
			$event->fromAddress(),
			$event->toAddress(),
			$event->tokenAddress(),
			$event->amount(),
			$event->transferType()
		);
	}

	/** 指定した請求書IDのトランザクションが書き込まれているブロック番号を取得します */
	public function getBlockNumber( InvoiceId $invoice_id ): ?BlockNumber {
		$record = $this->unlock_paywall_transaction_table->get( $invoice_id );
		return BlockNumber::fromIntNullable( $record ? $record->blockNumberValue() : null );
	}

	/**
	 * 購入時のトランザクション情報が記録されているかどうかを取得します
	 */
	public function exists( InvoiceId $invoice_id ): bool {
		// トランザクション情報が存在するか確認
		return $this->unlock_paywall_transaction_table->exists( $invoice_id );
	}
}
