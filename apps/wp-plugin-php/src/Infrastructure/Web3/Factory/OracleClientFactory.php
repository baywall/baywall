<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Factory;

use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Infrastructure\Web3\Client\OracleClient;

class OracleClientFactory {


	public function __construct( private readonly ChainRepository $chain_repository ) {}

	public function create( Oracle $oracle ): OracleClient {
		$rpc_url = $this->chain_repository->get( $oracle->chainId() )->rpcUrl();
		return new OracleClient( $rpc_url, $oracle->address() );
	}
}
