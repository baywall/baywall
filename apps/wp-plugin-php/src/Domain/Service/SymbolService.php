<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class SymbolService {

	private ChainRepository $chain_repository;
	private TokenRepository $token_repository;
	private OracleRepository $oracle_repository;

	public function __construct( ChainRepository $chain_repository, TokenRepository $token_repository, OracleRepository $oracle_repository ) {
		$this->chain_repository  = $chain_repository;
		$this->token_repository  = $token_repository;
		$this->oracle_repository = $oracle_repository;
	}

	/**
	 * 指定したシンボルが販売可能かどうかを判定します。
	 */
	public function isSellable( Symbol $symbol ): bool {
		$all_tokens            = $this->token_repository->all();
		$payable_symbol_tokens = ( new TokensFilter() )
			->bySymbol( $symbol )
			->byIsPayable( true )
			->apply( $all_tokens );

		// レート変換無しで支払い可能なトークンが存在する場合は販売可能
		// - ETHが支払い可能 => ETHで販売可能
		// - USDCが支払い可能 => USDCで販売可能
		if ( ! empty( $payable_symbol_tokens ) ) {
			return true;
		}

		// 接続可能なチェーンにあるQuoteSymbolがUSDのOracle一覧を取得
		$usd_quote_oracles = array_filter(
			( new OraclesFilter( $this->chain_repository ) )
			->byConnectable()
			->apply( $this->oracle_repository->all() ),
			function ( $oracle ) {
				return $oracle->symbolPair()->quote()->equals( Symbol::from( 'USD' ) );
			}
		);

		// symbolが'USD'以外の場合は、[symbol]/USDのOracleが存在しないとレート変換が出来ないため販売不可
		if ( ! $symbol->equals( Symbol::from( 'USD' ) ) ) {
			// [symbol]/USDのOracleを取得
			// ※ array_findはPH8.4以降でしか使えないためarray_filterで代用
			$symbol_usd_oracles = array_filter( $usd_quote_oracles, fn( $oracle ) => $oracle->symbolPair()->base()->equals( $symbol ) );

			// [symbol]/USDのOracleが存在しない場合はレート変換できないので販売不可
			if ( empty( $symbol_usd_oracles ) ) {
				return false;
			}
		}

		// symbolが'USD'または[symbol]/USDのOracleが存在する場合は、支払い可能なトークンに変換できる必要がある
		// まずは[symbol]/USD以外のXXX/USDのOracle一覧を取得
		$other_usd_quote_oracles = array_filter(
			$usd_quote_oracles,
			function ( $oracle ) use ( $symbol ) {
				return ! $oracle->symbolPair()->base()->equals( $symbol );
			}
		);
		// 通貨ペアがXXX/USDのXXXがトークンのシンボルかつ支払可能なものがあれば販売可能
		foreach ( $other_usd_quote_oracles as $oracle ) {
			$base_symbol        = $oracle->symbolPair()->base();
			$base_symbol_tokens = ( new TokensFilter() )
				->bySymbol( $base_symbol )
				->byIsPayable( true )
				->apply( $all_tokens );

			if ( ! empty( $base_symbol_tokens ) ) {
				return true;
			}
		}
		return false;
	}
}
