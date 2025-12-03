<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\Erc4361NonceProvider;
use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Nonce;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\WpErc4361NonceString;

class WpErc4361NonceProvider implements Erc4361NonceProvider {
	/** @inheritDoc */
	public function generate(): Erc4361Nonce {
		return Erc4361Nonce::from(
			WpErc4361NonceString::generate(),
			UnixTimestamp::now()
		);
	}
}
