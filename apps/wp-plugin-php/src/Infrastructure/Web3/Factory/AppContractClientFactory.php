<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Factory;

use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Abi\AppContractAbi;
use Cornix\Serendipity\Core\Infrastructure\Web3\AppContractClient;

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
