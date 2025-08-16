<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
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
	public function convertPriceToBaseUnit( Price $price, ChainId $chain_id ): Amount {
		// 対象のチェーンにおけるトークン情報を取得
		$token = $this->findTokenByChainAndSymbol( $chain_id, $price->symbol() );

		// 基本単位への変換倍率を取得
		$multiplier = $this->calculateBaseUnitMultiplier( $token->decimals() );

		// 計算結果を返す(小数点以下切り捨て)
		return $price->amount()->mul( $multiplier )->div( Amount::from( '1' ), Decimals::from( 0 ) );
	}

	/** チェーンIDとシンボルに合致するトークン情報を検索します */
	private function findTokenByChainAndSymbol( ChainId $chain_id, Symbol $symbol ): Token {
		$tokens = ( new TokensFilter() )
			->byChainId( $chain_id )
			->bySymbol( $symbol )
			->apply( $this->token_repository->all() );

		if ( 1 !== count( $tokens ) ) {
			throw new \InvalidArgumentException( "[30E8EDA4] Invalid token data. - chainId: {$chain_id}, symbol: {$symbol}, count: " . count( $tokens ) );
		}

		return array_values( $tokens )[0];
	}

	/** 小数点桁数から基本単位への変換倍数を計算します */
	private function calculateBaseUnitMultiplier( Decimals $decimals ): Amount {
		return Amount::from( (string) ( 10 ** $decimals->value() ) );
	}
}
