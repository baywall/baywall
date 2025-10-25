<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Base\Signer;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\PrivateKey;

/** サーバーの署名用ウォレット */
class ServerSigner extends Signer {
	/**
	 * @param Address    $address
	 * @param PrivateKey $private_key
	 */
	public function __construct(
		Address $address,
		#[\SensitiveParameter]
		PrivateKey $private_key
	) {
		parent::__construct( $address, $private_key );
	}
}
