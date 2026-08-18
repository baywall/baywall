<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\PostId;

/** Appコントラクトから必要なデータを取得するインターフェース */
interface AppContractDataProvider {
	/** 指定したチェーンで投稿が購入された時のブロック番号を取得します */
	public function unlockedBlockNumber( ChainId $chain_id, PostId $post_id, Address $buyer_address ): ?BlockNumber;
}
