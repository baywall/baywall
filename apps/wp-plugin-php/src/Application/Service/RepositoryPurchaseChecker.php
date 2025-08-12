<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;

class RepositoryPurchaseChecker {

	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;

	public function __construct(
		UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository
	) {
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
	}

	/**
	 * データベースを参照して購入済みかどうかを確認します
	 */
	public function isPurchased( InvoiceId $invoice_id ): bool {
		// トランザクション情報が存在する場合、購入済みと判定
		// ※ 返金処理が追加された場合、この処理は不整合を引き起こすので修正が必要
		return $this->unlock_paywall_transfer_event_repository->exists( $invoice_id );
	}
}
