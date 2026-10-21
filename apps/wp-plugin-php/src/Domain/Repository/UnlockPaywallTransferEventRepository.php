<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\UnlockPaywallTransferEvent;

interface UnlockPaywallTransferEventRepository {
	/** イベント情報を保存します */
	public function save( ChainId $chain_id, UnlockPaywallTransferEvent $event ): void;
}
