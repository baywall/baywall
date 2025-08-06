<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Closure;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\PrivateKey;

class Signer {

	/**
	 * @param PrivateKey|string $private_key
	 * @param Closure():Address $get_address_callback
	 * @disregard P1009 Undefined type
	 */
	public function __construct(
		#[\SensitiveParameter]
		PrivateKey $private_key,
		Closure $get_address_callback
	) {
		$this->private_key          = is_string( $private_key ) ? PrivateKey::from( $private_key ) : $private_key;
		$this->get_address_callback = $get_address_callback;
	}

	private PrivateKey $private_key;
	/** @var Closure():Address */
	private Closure $get_address_callback;

	public function privateKey(): PrivateKey {
		return $this->private_key;
	}

	/**
	 * ウォレットアドレスを取得します。
	 */
	public function address(): Address {
		return ( $this->get_address_callback )();
	}
}
