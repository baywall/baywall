<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\UnlockPaywallTransferEvent;

interface AppContractClient {
	/**
	 * ペイウォール解除時のトークン転送イベントを取得します。
	 *
	 * @param BlockNumber $from_block 開始ブロック番号
	 * @param BlockNumber $to_block   終了ブロック番号
	 * @param Address     $server_signer_address サーバーの署名用ウォレットアドレス
	 * @return UnlockPaywallTransferEvent[] イベントの配列
	 */
	public function getUnlockPaywallTransferEvents(
		BlockNumber $from_block,
		BlockNumber $to_block,
		Address $server_signer_address
	): array;
}
