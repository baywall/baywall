<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject\Base;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * 配列の値を表す基底クラス
 */
abstract class ArrayValueObject implements ValueObject {

	protected function __construct( array $array_value ) {
		$this->array_value = $array_value;
	}
	private array $array_value;

	public function value(): array {
		return $this->array_value;
	}

	public function __toString(): string {
		return json_encode( $this->array_value, JSON_THROW_ON_ERROR );
	}

	public function equals( self $other ): bool {
		// キーと値の組み合わせがすべて一致しているかどうかをチェック。
		// ※ キーの順序は問わない
		return array_diff_assoc( $this->array_value, $other->array_value ) === array() && array_diff_assoc( $other->array_value, $this->array_value ) === array();
	}
}
