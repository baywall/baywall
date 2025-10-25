<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity\Base;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\PrivateKey;

class Signer {

	/**
	 * @param Address    $address
	 * @param PrivateKey $private_key
	 */
	public function __construct(
		Address $address,
		#[\SensitiveParameter]
		PrivateKey $private_key
	) {
		$this->address     = $address;
		$this->private_key = $private_key;
	}

	private PrivateKey $private_key;
	private Address $address;

	/**
	 * 秘密鍵を取得します。
	 */
	public function privateKey(): PrivateKey {
		return $this->private_key;
	}

	/**
	 * ウォレットアドレスを取得します。
	 */
	public function address(): Address {
		return $this->address;
	}
}
