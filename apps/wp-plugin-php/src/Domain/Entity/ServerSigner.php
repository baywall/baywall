<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity;

use Baywall\Core\Domain\Entity\Base\Signer;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\PrivateKey;

/** サーバーの署名用ウォレット */
class ServerSigner extends Signer {
	/**
	 * @param Address    $address
	 * @param PrivateKey $private_key
	 * @disregard P1009 Undefined type
	 */
	public function __construct(
		Address $address,
		#[\SensitiveParameter]
		PrivateKey $private_key
	) {
		parent::__construct( $address, $private_key );
	}
}
