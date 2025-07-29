<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class TokenAmountConverter {
	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository = $token_repository;
	}

	private TokenRepository $token_repository;

	/**
	 * 指定した価格を対象のチェーンで使用する時の数量に変換します
	 */
	public function convertPriceToBaseUnit( Price $price, ChainID $chain_ID ): Amount {
		// 対象のチェーンにおけるトークン情報を取得
		$token = $this->findTokenByChainAndSymbol( $chain_ID, $price->symbol() );

		// 基本単位への変換倍率を取得
		$multiplier = $this->calculateBaseUnitMultiplier( $token->decimals() );

		return $price->amount()->mul( $multiplier );
	}

	/** チェーンIDとシンボルに合致するトークン情報を検索します */
	private function findTokenByChainAndSymbol( ChainID $chain_ID, Symbol $symbol ): Token {
		$tokens = ( new TokensFilter() )
			->byChainID( $chain_ID )
			->bySymbol( $symbol )
			->apply( $this->token_repository->all() );

		if ( 1 !== count( $tokens ) ) {
			throw new \InvalidArgumentException( "[30E8EDA4] Invalid token data. - chainID: {$chain_ID}, symbol: {$symbol}, count: " . count( $tokens ) );
		}

		return array_values( $tokens )[0];
	}

	/** 小数点桁数から基本単位への変換倍数を計算します */
	private function calculateBaseUnitMultiplier( Decimals $decimals ): Amount {
		return Amount::from( (string) ( 10 ** $decimals->value() ) );
	}
}
