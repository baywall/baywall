<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\NativeTokenConstants;

class NativeTokenService {
	/** 指定したチェーンのネイティブトークンシンボルを取得します (例: ETH) */
	public function getSymbol( ChainId $chain_id ): Symbol {
		$symbol_value = NativeTokenConstants::DEFINITIONS[ $chain_id->value() ]['symbol'] ?? null;
		assert( is_string( $symbol_value ), '[6BF3C03C]' );
		return Symbol::from( $symbol_value );
	}

	/** 指定したチェーンのネイティブトークン小数点以下桁数を取得します (例: 18) */
	public function getDecimals( ChainId $chain_id ): Decimals {
		$decimals_value = NativeTokenConstants::DEFINITIONS[ $chain_id->value() ]['decimals'] ?? null;
		assert( is_int( $decimals_value ), '[4519A9F1]' );
		return Decimals::from( $decimals_value );
	}

	/** 指定したチェーンのネイティブトークン名を取得します (例: Ether) */
	public function getName( ChainId $chain_id ): string {
		$name = NativeTokenConstants::DEFINITIONS[ $chain_id->value() ]['name'] ?? null;
		assert( is_string( $name ), '[0B6ABE0F]' );
		return $name;
	}
}
