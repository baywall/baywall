<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject\Base;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * 文字列の値を表す基底クラス
 */
abstract class StringValueObject implements ValueObject {

	protected function __construct( string $string_value ) {
		$this->string_value = $string_value;
	}
	private string $string_value;

	public function value(): string {
		return $this->string_value;
	}

	public function __toString(): string {
		return $this->string_value;
	}

	public function equals( self $other ): bool {
		return $this->string_value === $other->string_value;
	}
}
