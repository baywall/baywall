<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

/** JWT署名アルゴリズムを表すクラス */
final class JwtAlgorithm extends StringValueObject {

	private const ALG_HS256 = 'HS256';

	private function __construct( string $value ) {
		parent::__construct( $value );

		if ( in_array( $value, array( self::ALG_HS256 ), true ) === false ) {
			throw new \InvalidArgumentException( "[D635B96F] Unsupported JWT Algorithm: {$value}" );
		}
	}

	public static function from( string $value ): self {
		return new self( $value );
	}
}
