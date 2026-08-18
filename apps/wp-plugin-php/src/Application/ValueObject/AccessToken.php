<?php
declare(strict_types=1);

namespace Baywall\Core\Application\ValueObject;

use Baywall\Core\Domain\ValueObject\Base\SecureStringValueObject;

/**
 * アクセストークンを表すクラス
 */
class AccessToken extends SecureStringValueObject {

	private function __construct( string $access_token_value ) {
		parent::__construct( $access_token_value );
	}

	public static function from( string $access_token_value ): self {
		return new self( $access_token_value );
	}
}
