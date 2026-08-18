<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Factory;

use Baywall\Core\Domain\Repository\AppContractRepository;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Infrastructure\Web3\Abi\AppContractAbi;
use Baywall\Core\Infrastructure\Web3\Client\AppContractClient;

class AppContractClientFactory {

	private AppContractRepository $app_contract_repository;
	private AppContractAbi $app_contract_abi;

	public function __construct( AppContractRepository $app_contract_repository, AppContractAbi $app_contract_abi ) {
		$this->app_contract_repository = $app_contract_repository;
		$this->app_contract_abi        = $app_contract_abi;
	}

	/**
	 * 指定したチェーンにのAppコントラクトへ接続するオブジェクトを生成します。
	 */
	public function create( ChainId $chain_id ): AppContractClient {
		return new AppContractClient(
			$this->app_contract_repository->get( $chain_id ),
			$this->app_contract_abi
		);
	}
}
