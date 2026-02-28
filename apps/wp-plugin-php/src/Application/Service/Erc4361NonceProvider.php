<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Nonce;

interface Erc4361NonceProvider {
	/** ERC4361で使用するNonceを生成します */
	public function generate(): Erc4361Nonce;
}
