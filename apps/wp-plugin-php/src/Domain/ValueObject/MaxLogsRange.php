<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * ログ取得ブロック範囲最大値を表すクラス
 */
final class MaxLogsRange implements ValueObject {

	private function __construct( int $max_logs_range_value ) {
		self::checkMaxLogsRangeValue( $max_logs_range_value );
		$this->max_logs_range_value = $max_logs_range_value;
	}

	private readonly int $max_logs_range_value;

	public static function from( int $max_logs_range_value ): self {
		return new self( $max_logs_range_value );
	}

	public function value(): int {
		return $this->max_logs_range_value;
	}

	public function __toString(): string {
		return (string) $this->max_logs_range_value;
	}

	public function equals( self $other ): bool {
		return $this->max_logs_range_value === $other->max_logs_range_value;
	}

	/**
	 * ログ取得ブロック範囲最大値が正しい範囲であることを確認する
	 */
	private static function checkMaxLogsRangeValue( int $max_logs_range_value ): void {
		if ( $max_logs_range_value < 1 || $max_logs_range_value > Config::GET_LOGS_MAX_RANGE ) {
			throw new \InvalidArgumentException( '[5F7E23AA] Invalid max logs range value. Must be between 1 and ' . Config::GET_LOGS_MAX_RANGE . '. - ' . $max_logs_range_value );
		}
	}
}
