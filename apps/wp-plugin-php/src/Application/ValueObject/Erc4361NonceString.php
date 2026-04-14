<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\SecureStringValueObject;

/**
 * ERC-4361 Nonce 文字列を表すクラス
 */
class Erc4361NonceString extends SecureStringValueObject {

	protected function __construct( string $nonce_string_value ) {
		parent::__construct( $nonce_string_value );
	}

	public static function from( string $nonce_string_value ): self {
		return new self( $nonce_string_value );
	}
}
