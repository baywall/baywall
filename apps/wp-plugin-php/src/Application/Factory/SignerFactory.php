<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Factory;

use Cornix\Serendipity\Core\Domain\Entity\Signer;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\PrivateKey;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;

class SignerFactory {
	/**
	 * @param PrivateKey|string $private_key
	 * @disregard P1009 Undefined type
	 */
	public function create(
		#[\SensitiveParameter]
		PrivateKey $private_key
	): Signer {
		$get_address_callback = fn (): Address => Ethers::privateKeyToAddress( $private_key );
		return new Signer(
			$private_key,
			$get_address_callback
		);
	}
}
