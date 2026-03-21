<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

/** Appコントラクトから必要なデータを取得するインターフェース */
interface AppContractDataProvider {
	/** 指定したチェーンで投稿が購入された時のブロック番号を取得します */
	public function unlockedBlockNumber( ChainId $chain_id, PostId $post_id, Address $customer_address ): ?BlockNumber;
}
