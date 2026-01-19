<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject\Base;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;
use Cornix\Serendipity\Core\Infrastructure\Util\Strings;

/**
 * URLを表すValueObjectの基底クラス
 */
abstract class UrlBase {

	protected function __construct( string $url_value ) {
		static::checkValidUrlFormat( $url_value );
		$this->url_value = $url_value;
	}
	private string $url_value;

	public function value(): string {
		return $this->url_value;
	}

	public function __toString(): string {
		return $this->url_value;
	}

	protected static function checkValidUrlFormat( string $url_value ): void {
		$is_url = filter_var( $url_value, FILTER_VALIDATE_URL ) !== false && Strings::starts_with( $url_value, 'http' );
		if ( ! $is_url ) {
			throw new \InvalidArgumentException( '[762B3EE8] Invalid URL format. ' . $url_value );
		}
	}
}
