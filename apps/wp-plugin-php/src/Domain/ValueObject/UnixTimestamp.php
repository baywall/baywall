<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;
use DateTimeImmutable;

class UnixTimestamp implements ValueObject {


	private function __construct( private readonly int $timestamp ) {}

	public static function from( int $timestamp ): self {
		return new self( $timestamp );
	}

	/**
	 * RFC3339形式の文字列を取得します
	 *
	 * 例: `2025-11-29T01:45:50+00:00`
	 */
	public function toRfc3339Value(): string {
		return ( new DateTimeImmutable() )->setTimestamp( $this->timestamp )->format( DateTimeImmutable::RFC3339 );
	}

	public function value(): int {
		return $this->timestamp;
	}

	/** 指定した秒数を加算した新しいUnixTimestampインスタンスを返します */
	public function addSeconds( int $seconds ): self {
		return self::from( $this->timestamp + $seconds );
	}

	public static function now(): self {
		return new self( time() );
	}

	public function equals( self $other ): bool {
		return $this->timestamp === $other->timestamp;
	}

	public function __toString(): string {
		return (string) $this->timestamp;
	}
}
