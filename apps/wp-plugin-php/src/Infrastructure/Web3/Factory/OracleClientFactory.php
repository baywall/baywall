<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Factory;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Infrastructure\Web3\OracleClient;

class OracleClientFactory {
	public function create( Oracle $oracle ): OracleClient {
		return new OracleClient( $oracle->chain()->rpcUrl(), $oracle->address() );
	}
}
