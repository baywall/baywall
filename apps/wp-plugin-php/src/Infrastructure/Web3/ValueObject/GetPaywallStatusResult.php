<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

class GetPaywallStatusResult {
	public function __construct( bool $is_unlocked, InvoiceId $invoice_id, BlockNumber $unlocked_block_number ) {
		$this->is_unlocked           = $is_unlocked;
		$this->invoice_id            = $invoice_id;
		$this->unlocked_block_number = $unlocked_block_number;
	}
	private bool $is_unlocked;
	private InvoiceId $invoice_id;
	private BlockNumber $unlocked_block_number;

	/** ペイウォールが解除済みかどうかを取得します。 */
	public function isUnlocked(): bool {
		return $this->is_unlocked;
	}

	/** ペイウォールを解除した時の請求書IDを取得します。 */
	public function invoiceId(): InvoiceId {
		return $this->invoice_id;
	}

	/** ペイウォールを解除した時のブロック番号を取得します。 */
	public function unlockedBlockNumber(): BlockNumber {
		return $this->unlocked_block_number;
	}
}
