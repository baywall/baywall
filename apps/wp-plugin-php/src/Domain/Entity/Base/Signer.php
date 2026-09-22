<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity\Base;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\PrivateKey;

class Signer {

	/**
	 * @param Address    $address
	 * @param PrivateKey $private_key
	 * @disregard P1009 Undefined type
	 */
	public function __construct(
		private readonly Address $address,
		#[\SensitiveParameter] private readonly PrivateKey $private_key
	) {}


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
