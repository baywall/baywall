<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Auth;

use Baywall\Core\Application\Service\Clock;
use Baywall\Core\Application\Service\Erc4361NonceGenerator;
use Baywall\Core\Application\ValueObject\Erc4361Nonce;
use Baywall\Core\Infrastructure\WordPress\ValueObject\WpErc4361NonceString;

class WpErc4361NonceGenerator implements Erc4361NonceGenerator {

	public function __construct( private readonly Clock $clock ) {}

	/** @inheritDoc */
	public function generate(): Erc4361Nonce {
		return Erc4361Nonce::from(
			WpErc4361NonceString::generate(),
			$this->clock->now()
		);
	}
}
