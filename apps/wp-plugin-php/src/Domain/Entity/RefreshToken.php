<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * リフレッシュトークンを表すクラス
 */
class RefreshToken extends StringValueObject {
	private function __construct( string $refresh_token_value ) {
		parent::__construct( $refresh_token_value );
	}

	public function from( string $refresh_token_value ): self {
		return new self( $refresh_token_value );
	}
}
