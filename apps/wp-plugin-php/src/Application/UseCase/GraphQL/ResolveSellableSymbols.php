<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\SymbolService;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class ResolveSellableSymbols {

	private UserAccessChecker $user_access_checker;
	private TokenRepository $token_repository;
	private OracleRepository $oracle_repository;
	private SymbolService $symbol_service;

	public function __construct(
		UserAccessChecker $user_access_checker,
		TokenRepository $token_repository,
		OracleRepository $oracle_repository,
		SymbolService $symbol_service
	) {
		$this->user_access_checker = $user_access_checker;
		$this->token_repository    = $token_repository;
		$this->oracle_repository   = $oracle_repository;
		$this->symbol_service      = $symbol_service;
	}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkCanCreatePost();   // 投稿を新規作成できる権限が必要

		/**
		 * 通貨シンボルの文字列をキーとする連想配列。後でキーだけを取り出して重複排除するために使用。
		 *
		 * @var array<string,true>
		 */
		$symbol_value_set = array();

		// トークン一覧のシンボルを追加
		foreach ( $this->token_repository->all() as $token ) {
			$symbol_value_set[ $token->symbol()->value() ] = true;
		}
		// Oracleのbaseとquoteのシンボルを追加
		foreach ( $this->oracle_repository->all() as $oracle ) {
			$symbol_value_set[ $oracle->symbolPair()->base()->value() ]  = true;
			$symbol_value_set[ $oracle->symbolPair()->quote()->value() ] = true;
		}

		// 販売可能なシンボルのみを抽出して返す
		return array_values(
			array_filter(
				array_keys( $symbol_value_set ),
				fn( $symbol_value ) => $this->symbol_service->isSellable( Symbol::from( $symbol_value ) ),
			)
		);
	}
}
