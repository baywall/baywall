<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Factory;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Infrastructure\Web3\OracleClient;

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
