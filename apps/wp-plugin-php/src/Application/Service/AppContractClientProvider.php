<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\ValueObject\ChainId;

interface AppContractClientProvider {
	/** 指定したチェーンのAppコントラクトへ接続するクライアントを生成します。 */
	public function create( ChainId $chain_id ): AppContractClient;
}
