<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3;

use Baywall\Core\Infrastructure\Retry\Retryer;
use Baywall\Core\Constant\Config;

class BlockchainRetryer {
	public function __construct() {
		$this->intervals_ms = Config::BLOCKCHAIN_REQUEST_RETRY_INTERVALS_MS;
	}
	/** @var int[] */
	private array $intervals_ms;

	public function execute( callable $callback ) {
		return ( new Retryer() )->execute( $callback, $this->intervals_ms );
	}
}
