<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * アクセストークンを表すクラス
 */
class AccessToken extends StringValueObject {

	private function __construct( string $access_token_value ) {
		parent::__construct( $access_token_value );
	}

	public static function from( string $access_token_value ): self {
		return new self( $access_token_value );
	}
}
