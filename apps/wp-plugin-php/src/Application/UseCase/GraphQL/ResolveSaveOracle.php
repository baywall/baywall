<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Infrastructure\Web3\OracleClient;

/**
 * Oracle情報をサーバーに登録します。
 */
class ResolveSaveOracle {

	private UserAccessChecker $user_access_checker;
	private OracleRepository $oracle_repository;
	private ChainRepository $chain_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		OracleRepository $oracle_repository,
		ChainRepository $chain_repository
	) {
		$this->user_access_checker = $user_access_checker;
		$this->oracle_repository   = $oracle_repository;
		$this->chain_repository    = $chain_repository;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );
		$address  = Address::from( $args['address'] );

		// Oracleがデプロイされているチェーンの情報を取得
		$chain = $this->chain_repository->get( $chain_id );
		if ( null === $chain ) {
			throw new \RuntimeException( "[018783AA] Chain not found. chain ID: {$chain_id}" );
		}
		$rpc_url = $chain->rpcUrl();
		if ( null === $rpc_url ) {
			throw new \RuntimeException( "[F60696E9] RPC URL not set. chain ID: {$chain_id}" );
		}

		// チェーンに接続してOracleコントラクトからシンボルペアを取得する
		$oracle_client = new OracleClient( $rpc_url, $address );
		$description   = $oracle_client->description();
		$symbols       = $description ? explode( '/', $description ) : array();
		$base_symbol   = $symbols[0] ? Symbol::from( trim( $symbols[0] ) ) : null;
		$quote_symbol  = $symbols[1] ? Symbol::from( trim( $symbols[1] ) ) : null;

		// QuoteシンボルはUSDかETHのみ許容
		if ( ! $quote_symbol->equals( Symbol::from( 'USD' ) ) && ! $quote_symbol->equals( Symbol::from( 'ETH' ) ) ) {
			throw new \RuntimeException( "[22BD013C] Unsupported quote symbol. quote symbol: {$quote_symbol}" );
		}

		// 登録するOracle情報を作成
		$oracle = new Oracle( $chain, $address, SymbolPair::from( $base_symbol, $quote_symbol ) );

		// Oracle情報を保存
		$this->oracle_repository->save( $oracle );

		return true;
	}
}
