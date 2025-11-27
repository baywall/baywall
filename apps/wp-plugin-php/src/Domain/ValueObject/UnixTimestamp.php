<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;
use DateTimeImmutable;

class UnixTimestamp implements ValueObject {

	private int $timestamp;

	private function __construct( int $timestamp ) {
		$this->timestamp = $timestamp;
	}

	public static function from( int $timestamp ): self {
		return new self( $timestamp );
	}

	public static function fromMySql( string $mysql_datetime ): self {
		$datetime = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $mysql_datetime );
		if ( false === $datetime ) {
			throw new \InvalidArgumentException( '[5D7C87DE] Invalid MySQL DATETIME format: ' . $mysql_datetime );
		}
		return self::from( $datetime->getTimestamp() );
	}

	public static function fromMySqlNullable( ?string $mysql_datetime ): ?self {
		return $mysql_datetime === null ? null : self::fromMySql( $mysql_datetime );
	}

	public function toMySqlValue(): string {
		return ( new DateTimeImmutable() )->setTimestamp( $this->timestamp )->format( 'Y-m-d H:i:s' );
	}

	public function value(): int {
		return $this->timestamp;
	}

	public static function now(): self {
		return new self( time() );
	}

	public function __toString(): string {
		return $this->toMySqlValue();
	}
}
