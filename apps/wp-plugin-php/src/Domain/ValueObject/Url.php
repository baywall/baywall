<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * URLを表すValueObjectクラス
 */
class Url implements ValueObject {

	protected function __construct( string $url_value ) {
		static::checkValidUrlFormat( $url_value );
		$this->url_value = $url_value;
	}
	private readonly string $url_value;

	public static function from( string $url_value ): self {
		return new self( $url_value );
	}

	public function value(): string {
		return $this->url_value;
	}

	public function __toString(): string {
		return $this->url_value;
	}

	public function equals( self $other ): bool {
		return $this->value() === $other->value();
	}

	protected static function checkValidUrlFormat( string $url_value ): void {
		$is_url = filter_var( $url_value, FILTER_VALIDATE_URL ) !== false && str_starts_with( $url_value, 'http' );
		if ( ! $is_url ) {
			throw new \InvalidArgumentException( '[762B3EE8] Invalid URL format. ' . $url_value );
		}
	}
}
