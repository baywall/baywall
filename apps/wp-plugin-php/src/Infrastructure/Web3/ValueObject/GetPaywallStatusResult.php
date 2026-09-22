<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\ValueObject;

use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\InvoiceId;

class GetPaywallStatusResult {
	public function __construct(
		private readonly bool $is_unlocked,
		private readonly ?InvoiceId $invoice_id,
		private readonly ?BlockNumber $unlocked_block_number
	) {}

	/** ペイウォールが解除済みかどうかを取得します。 */
	public function isUnlocked(): bool {
		return $this->is_unlocked;
	}

	/** ペイウォールを解除した時の請求書IDを取得します。 */
	public function invoiceId(): ?InvoiceId {
		return $this->invoice_id;
	}

	/** ペイウォールを解除した時のブロック番号を取得します。 */
	public function unlockedBlockNumber(): ?BlockNumber {
		return $this->unlocked_block_number;
	}
}
