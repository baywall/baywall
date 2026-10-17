<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\Service\OracleService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

/**
 * Oracle情報をサーバーに登録します。
 */
class ResolveSaveOracle {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly OracleRepository $oracle_repository,
		private readonly ChainRepository $chain_repository,
		private readonly OracleService $oracle_service
	) {}

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
		$symbol_pair = $this->oracle_service->symbolPair( $rpc_url, $address );

		// 登録するOracle情報を作成
		$oracle = new Oracle( $chain->id(), $address, $symbol_pair );

		// Oracle情報を保存
		$this->oracle_repository->save( $oracle );

		return true;
	}
}
