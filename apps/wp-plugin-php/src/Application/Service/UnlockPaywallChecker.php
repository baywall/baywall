<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;

/** ペイウォールが解除済みかどうかを取得するクラス */
class UnlockPaywallChecker {

	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;

	public function __construct( UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository ) {
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
	}

	/** 指定された請求書IDのペイウォールが解除済みかどうかを取得します */
	public function isPaywallUnlocked( InvoiceId $invoice_id ): bool {
		// ペイウォール解除時のブロック番号が取得できれば解除済みと判定
		$block_number = $this->unlock_paywall_transfer_event_repository->getBlockNumber( $invoice_id );
		return $block_number !== null;
	}
}
