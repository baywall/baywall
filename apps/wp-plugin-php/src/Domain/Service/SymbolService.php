<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class SymbolService {

	public function __construct( OracleRepository $oracle_repository ) {
		$this->oracle_repository = $oracle_repository;
	}
	private OracleRepository $oracle_repository;

	/**
	 * 販売価格として設定可能な通貨シンボル一覧を取得します。
	 *
	 * @return Symbol[]
	 */
	public function getSellableSymbols(): array {
		// 方針: Oracleテーブルに登録されているbase及びquoteの通貨シンボルは販売可能な通貨シンボルとして扱う。
		// 　　　その上で、現時点で販売価格として設定できるものはRPC URLが設定されているものとする。

		// 接続可能なoracle一覧を取得
		$oracles = ( new OraclesFilter() )
			->byConnectable()
			->apply( $this->oracle_repository->all() );

		// baseとquoteの通貨シンボルをプリミティブ型で取得
		/** @var string[] */
		$symbols = array();
		foreach ( $oracles as $oracle ) {
			$symbols[] = $oracle->symbolPair()->base()->value();
			$symbols[] = $oracle->symbolPair()->quote()->value();
		}

		// 重複を削除
		$symbols = array_unique( $symbols );

		// Symbol型に変換してから返す
		return array_map(
			fn( string $symbol ) => new Symbol( $symbol ),
			$symbols
		);
	}
}
