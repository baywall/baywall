<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Domain\ValueObject\Symbol;

interface NativeTokenProvider {
	/** 指定したチェーンのネイティブトークンシンボルを取得します (例: ETH) */
	public function getSymbol( ChainId $chain_id ): Symbol;

	/** 指定したチェーンのネイティブトークン小数点以下桁数を取得します (例: 18) */
	public function getDecimals( ChainId $chain_id ): Decimals;

	/** 指定したチェーンのネイティブトークン名を取得します (例: Ether) */
	public function getName( ChainId $chain_id ): string;
}
