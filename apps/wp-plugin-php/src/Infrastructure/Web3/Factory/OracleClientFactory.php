<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Factory;

use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Infrastructure\Web3\Client\OracleClient;

class OracleClientFactory {

	private ChainRepository $chain_repository;

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	public function create( Oracle $oracle ): OracleClient {
		$rpc_url = $this->chain_repository->get( $oracle->chainId() )->rpcUrl();
		return new OracleClient( $rpc_url, $oracle->address() );
	}
}
