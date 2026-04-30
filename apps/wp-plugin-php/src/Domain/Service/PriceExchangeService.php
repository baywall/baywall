<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Exception\PriceExchangeException;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;

class PriceExchangeService {

	/**
	 * 計算を行う際の小数点以下の精度。
	 *
	 * MySQLの型がdecimal(65,30)なので30桁で計算しておく。
	 */
	private const ACCURACY_DECIMALS = 30;

	/** 変換不要（同一通貨） */
	private const EXCHANGE_METHOD_PASS_THROUGH = 'pass_through';
	/** 直接レート変換可能 */
	private const EXCHANGE_METHOD_DIRECT = 'direct';
	/** USD経由でのレート変換 */
	private const EXCHANGE_METHOD_VIA_USD = 'via_usd';
	/** ETH経由でのレート変換 */
	private const EXCHANGE_METHOD_VIA_ETH = 'via_eth';
	/** レート変換不可 */
	private const EXCHANGE_METHOD_UNSUPPORTED = 'unsupported';


	public function __construct( RateProvider $rate_provider ) {
		$this->rate_provider = $rate_provider;
	}

	private RateProvider $rate_provider;

	/** 指定された通貨ペアが交換可能かどうかを取得します */
	public function exchangeable( Price $price, Symbol $to_symbol ): bool {
		$method = $this->exchangeMethod( $price, $to_symbol );
		return $method !== self::EXCHANGE_METHOD_UNSUPPORTED;
	}

	/** レート変換の方法を取得します */
	private function exchangeMethod( Price $price, Symbol $to_symbol ): string {
		$from_symbol = $price->symbol();

		if ( $from_symbol->equals( $to_symbol ) ) {
			return self::EXCHANGE_METHOD_PASS_THROUGH;
		}

		// 直接変換可能な場合
		// - ETH -> USDC の時に ETH/USDC または USDC/ETH のレート変換がサポートされている場合
		if ( $this->rate_provider->supports( SymbolPair::from( $from_symbol, $to_symbol ) ) ||
			$this->rate_provider->supports( SymbolPair::from( $to_symbol, $from_symbol ) ) ) {
			return self::EXCHANGE_METHOD_DIRECT;
		}

		// USDを経由して変換可能な場合
		// - 例: ETH -> USDC の時に ETH/USD及びUSDC/USDのレート変換がサポートされている場合
		// ※ USDはquote側にしか存在しない想定
		if ( $this->rate_provider->supports( SymbolPair::from( $from_symbol, Symbol::from( 'USD' ) ) ) &&
			$this->rate_provider->supports( SymbolPair::from( $to_symbol, Symbol::from( 'USD' ) ) ) ) {
			return self::EXCHANGE_METHOD_VIA_USD;
		}

		// ETHを経由して変換可能な場合
		// - 例: BAT -> LINK の時に BAT/ETH及びLINK/ETHのレート変換がサポートされている場合
		// ※ ETHはquote側にしか存在しない想定
		if ( $this->rate_provider->supports( SymbolPair::from( $from_symbol, Symbol::from( 'ETH' ) ) ) &&
			$this->rate_provider->supports( SymbolPair::from( $to_symbol, Symbol::from( 'ETH' ) ) ) ) {
			return self::EXCHANGE_METHOD_VIA_ETH;
		}

		// 複数の組み合わせ（XXX/USDとXXX/ETH）を使ってのレート変換は現時点でサポートしない
		return self::EXCHANGE_METHOD_UNSUPPORTED;
	}

	public function exchange( Price $price, Symbol $to_symbol ): Price {
		$method      = $this->exchangeMethod( $price, $to_symbol );
		$from_symbol = $price->symbol();

		if ( $method === self::EXCHANGE_METHOD_UNSUPPORTED ) {
			throw new PriceExchangeException( "[2E9C84B0] Rate conversion failed. {$from_symbol} => {$to_symbol}" );
		}

		if ( $method === self::EXCHANGE_METHOD_PASS_THROUGH ) {
			// そのまま返す場合
			assert( $from_symbol->equals( $to_symbol ), "[E11DCB70] {$from_symbol} => {$to_symbol}" );
			return $price;
		} elseif ( $method === self::EXCHANGE_METHOD_DIRECT ) {
			// 直接変換可能な場合
			$rate = $this->rate_provider->supports( SymbolPair::from( $from_symbol, $to_symbol ) )
				? $this->rate_provider->getRate( SymbolPair::from( $from_symbol, $to_symbol ) )
				: $this->rate_provider->getRate( SymbolPair::from( $to_symbol, $from_symbol ) );
			return $this->calculatePrice( $price, $rate );
		} elseif ( $method === self::EXCHANGE_METHOD_VIA_USD ) {
			// USDを経由して変換可能な場合
			$from_usd  = $this->rate_provider->getRate( SymbolPair::from( $from_symbol, Symbol::from( 'USD' ) ) );
			$usd_to    = $this->rate_provider->getRate( SymbolPair::from( $to_symbol, Symbol::from( 'USD' ) ) );
			$usd_price = $this->calculatePrice( $price, $from_usd );
			return $this->calculatePrice( $usd_price, $usd_to );
		} elseif ( $method === self::EXCHANGE_METHOD_VIA_ETH ) {
			// ETHを経由して変換可能な場合
			$from_eth  = $this->rate_provider->getRate( SymbolPair::from( $from_symbol, Symbol::from( 'ETH' ) ) );
			$eth_to    = $this->rate_provider->getRate( SymbolPair::from( $to_symbol, Symbol::from( 'ETH' ) ) );
			$eth_price = $this->calculatePrice( $price, $from_eth );
			return $this->calculatePrice( $eth_price, $eth_to );
		} else {
			throw new \LogicException( "[C367BCDB] Unsupported exchange method: {$method}" );
		}
	}

	/** レート変換後の価格を取得します */
	private function calculatePrice( Price $price, Rate $rate ): Price {
		// レートの向きを価格の通貨シンボルがbaseになるように調整
		$rate = $price->symbol()->equals( $rate->symbolPair()->base() )
			? $rate
			: $rate->invert( Decimals::from( self::ACCURACY_DECIMALS ) );

		return Price::from(
			$price->amount()->mul( $rate->amount() ),
			$rate->symbolPair()->quote()
		);
	}
}
