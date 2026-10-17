<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\Specification\ChainsSpecification;
use Baywall\Core\Domain\Specification\OraclesSpecification;
use Baywall\Core\Domain\Specification\TokensSpecification;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\Symbol;

class SymbolService {


	public function __construct(
		private readonly ChainRepository $chain_repository,
		private readonly TokenRepository $token_repository,
		private readonly OracleRepository $oracle_repository
	) {}

	/**
	 * 指定したネットワークカテゴリで販売可能なシンボルを一括判定して返します。
	 * chain / token / oracle の all() は各 1 回のみ（シンボル件数に比例しない）。
	 *
	 * @return Symbol[]
	 */
	public function sellableSymbols( NetworkCategoryId $network_category_id ): array {
		$all_chains  = $this->chain_repository->all();
		$all_tokens  = $this->token_repository->all();
		$all_oracles = $this->oracle_repository->all();

		return array_values(
			array_filter(
				$this->symbolsFrom( $all_tokens, $all_oracles ),
				fn( $symbol ) => $this->isSellableFrom( $symbol, $network_category_id, $all_chains, $all_tokens, $all_oracles )
			)
		);
	}

	/**
	 * 販売可否の判定本体。I/O を伴わず、引数で受け取った各 all() の結果のみで判定します。
	 *
	 * @param Chain[]  $all_chains
	 * @param Token[]  $all_tokens
	 * @param Oracle[] $all_oracles
	 */
	private function isSellableFrom( Symbol $symbol, NetworkCategoryId $network_category_id, array $all_chains, array $all_tokens, array $all_oracles ): bool {
		/**
		 * 対象ネットワークの chain ID（`ChainId::value()`: int）をキーとする連想配列。isset での純粋判定に使用。
		 *
		 * @var array<int,true>
		 */
		$target_chain_id_set = array_fill_keys(
			array_map(
				fn( $chain ) => $chain->id()->value(),
				array_filter( $all_chains, ( new ChainsSpecification( network_category_id: $network_category_id ) )->isSatisfiedBy( ... ) )
			),
			true
		);

		// 指定したネットワークに存在するトークン一覧を取得（chain 未登録トークンは除外される）
		$target_network_all_tokens = array_filter(
			$all_tokens,
			fn( $token ) => isset( $target_chain_id_set[ $token->chainId()->value() ] )
		);
		$payable_symbol_tokens     = array_filter(
			$target_network_all_tokens,
			( new TokensSpecification( symbol: $symbol, is_payable: true ) )->isSatisfiedBy( ... )
		);

		// レート変換無しで支払可能なトークンが存在する場合は販売可能
		// - ETHが支払可能 => ETHで販売可能
		// - USDCが支払可能 => USDCで販売可能
		if ( ! empty( $payable_symbol_tokens ) ) {
			return true;
		}

		// 接続可能なチェーンにあるQuoteSymbolがUSDのOracle一覧を取得
		$connectable_chain_ids = array_map(
			fn( $chain ) => $chain->id(),
			array_filter( $all_chains, ( new ChainsSpecification( is_connectable: true ) )->isSatisfiedBy( ... ) )
		);
		$usd_quote_oracles     = array_filter(
			array_filter(
				$all_oracles,
				( new OraclesSpecification( connectable_chain_ids: $connectable_chain_ids ) )->isSatisfiedBy( ... )
			),
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

		// symbolが'USD'または[symbol]/USDのOracleが存在する場合は、支払可能なトークンに変換できる必要がある
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
			$base_symbol_tokens = array_filter(
				$target_network_all_tokens,
				( new TokensSpecification( symbol: $base_symbol, is_payable: true ) )->isSatisfiedBy( ... )
			);

			if ( ! empty( $base_symbol_tokens ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * シンボル導出の純粋処理。トークンのシンボルと Oracle の base / quote を出現順に重複排除します。
	 *
	 * @param Token[]  $all_tokens
	 * @param Oracle[] $all_oracles
	 * @return Symbol[]
	 */
	private function symbolsFrom( array $all_tokens, array $all_oracles ): array {
		/**
		 * 通貨シンボルの文字列をキーとする連想配列。後でキーだけを取り出して重複排除するために使用。
		 *
		 * @var array<string,true>
		 */
		$symbol_value_set = array();

		// トークン一覧のシンボルを追加
		foreach ( $all_tokens as $token ) {
			$symbol_value_set[ $token->symbol()->value() ] = true;
		}
		// Oracleのbaseとquoteのシンボルを追加
		foreach ( $all_oracles as $oracle ) {
			$symbol_value_set[ $oracle->symbolPair()->base()->value() ]  = true;
			$symbol_value_set[ $oracle->symbolPair()->quote()->value() ] = true;
		}

		return array_map(
			fn( $symbol_value ) => Symbol::from( $symbol_value ),
			array_keys( $symbol_value_set )
		);
	}
}
