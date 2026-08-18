<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\Erc4361NonceProvider;
use Baywall\Core\Application\ValueObject\Erc4361Nonce;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\ValueObject\WpErc4361NonceString;

class WpErc4361NonceProvider implements Erc4361NonceProvider {
	/** @inheritDoc */
	public function generate(): Erc4361Nonce {
		return Erc4361Nonce::from(
			WpErc4361NonceString::generate(),
			UnixTimestamp::now()
		);
	}
}
