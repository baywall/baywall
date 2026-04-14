<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject\Base;

/**
 * 推測されるとまずい文字列の値を表す基底クラス
 *
 * equalsではタイミング攻撃耐性のある比較を行う。
 */
abstract class SecureStringValueObject extends StringValueObject {

	protected function __construct( string $string_value ) {
		parent::__construct( $string_value );
	}

	public function equals( StringValueObject $other ): bool {
		return hash_equals( $this->value(), $other->value() );
	}
}
