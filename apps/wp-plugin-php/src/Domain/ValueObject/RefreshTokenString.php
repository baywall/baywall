<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * リフレッシュトークン文字列を表すクラス
 *
 * - リフレッシュトークン文字列のフォーマットは実装次第のため、ここではチェックしない
 */
class RefreshTokenString extends StringValueObject {
	private function __construct( string $refresh_token_value ) {
		parent::__construct( $refresh_token_value );
	}

	public static function from( string $refresh_token_value ): self {
		return new self( $refresh_token_value );
	}
}
