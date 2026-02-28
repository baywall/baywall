<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Exception\PriceExchangeException;
use Cornix\Serendipity\Core\Domain\Exception\RateNotFoundException;
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

	public function __construct( RateProvider $rate_provider ) {
		$this->rate_provider = $rate_provider;
	}

	private RateProvider $rate_provider;

	public function exchange( Price $price, Symbol $to_symbol ): Price {
		if ( $price->symbol()->equals( $to_symbol ) ) {
			return $price;  // 同じ通貨の場合は変換不要
		}

		$direct_rate = $this->resolveRate( $price->symbol(), $to_symbol );
		if ( ! is_null( $direct_rate ) ) {
			// 直接変換可能な場合
			return $this->calculatePrice( $price, $direct_rate );
		}

		$from_usd = $this->resolveRate( $price->symbol(), Symbol::from( 'USD' ) );
		$usd_to   = $this->resolveRate( Symbol::from( 'USD' ), $to_symbol );
		if ( ! is_null( $from_usd ) && ! is_null( $usd_to ) ) {
			// USDを経由して変換可能な場合
			$usd_price = $this->calculatePrice( $price, $from_usd );
			return $this->calculatePrice( $usd_price, $usd_to );
		}

		$from_eth = $this->resolveRate( $price->symbol(), Symbol::from( 'ETH' ) );
		$eth_to   = $this->resolveRate( Symbol::from( 'ETH' ), $to_symbol );
		if ( ! is_null( $from_eth ) && ! is_null( $eth_to ) ) {
			// ETHを経由して変換可能な場合
			$eth_price = $this->calculatePrice( $price, $from_eth );
			return $this->calculatePrice( $eth_price, $eth_to );
		}

		// ETH,USDを経由して変換可能な場合, USD,ETHを経由して変換可能な場合は現時点で実装しない
		throw new PriceExchangeException( "[2E9C84B0] Rate conversion failed. {$price->symbol()->value()} => {$to_symbol->value()}" );
	}

	private function calculatePrice( Price $price, Rate $rate ): Price {
		// resolveRateメソッドにより、常にprice->symbol() == rate->symbolPair()->base()が保証される
		if ( ! $price->symbol()->equals( $rate->symbolPair()->base() ) ) {
			throw new \LogicException( '[FFFE7145] Price symbol does not match rate base symbol' );
		}

		// レートを使って通貨変換を実行
		$amount = $price->amount()->mul( $rate->amount() );
		return Price::from( $amount, $rate->symbolPair()->quote() );
	}

	/**
	 * 指定された通貨シンボルのレート変換を行うためのRateインスタンスを取得します。
	 * from_symbolがbaseになるように調整されたRateを返します。
	 */
	private function resolveRate( Symbol $from_symbol, Symbol $to_symbol ): ?Rate {
		try {
			// 直接的なレートを取得を試す
			return $this->rate_provider->getRate( SymbolPair::from( $from_symbol, $to_symbol ) );
		} catch ( RateNotFoundException $_ ) {
			try {
				// 直接的なレートが見つからない場合、逆方向のレートを取得してinvert()する
				$reverse_rate = $this->rate_provider->getRate( SymbolPair::from( $to_symbol, $from_symbol ) );
				return $reverse_rate->invert( Decimals::from( self::ACCURACY_DECIMALS ) );
			} catch ( RateNotFoundException $_ ) {
				// 逆方向のレートも見つからない場合はnullを返す
				return null;
			}
		}
	}
}
