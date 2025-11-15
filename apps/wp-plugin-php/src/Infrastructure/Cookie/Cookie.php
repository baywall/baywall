<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\Cookie;

/**
 * Cookieに書き込むプロパティを表すクラス
 */
class Cookie {

	private string $name;
	private string $value;
	private ?int $expires;
	private ?string $path;
	private ?string $domain;
	private ?bool $secure;
	private ?bool $httpOnly;
	private ?string $sameSite;

	private function __construct( string $name, string $value, ?int $expires = null, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httpOnly = null, ?string $sameSite = null ) {
		$this->name     = $name;
		$this->value    = $value;
		$this->expires  = $expires;
		$this->path     = $path;
		$this->domain   = $domain;
		$this->secure   = $secure;
		$this->httpOnly = $httpOnly;
		$this->sameSite = $sameSite;
	}

	public static function create( string $name, string $value, ?int $expires = null, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httpOnly = null, ?string $sameSite = null ): self {
		return new self( $name, $value, $expires, $path, $domain, $secure, $httpOnly, $sameSite );
	}

	public function name(): string {
		return $this->name;
	}

	public function value(): string {
		return $this->value;
	}

	public function options(): array {
		$options = array();
		if ( $this->expires !== null ) {
			$options['expires'] = $this->expires;
		}
		if ( $this->path !== null ) {
			$options['path'] = $this->path;
		}
		if ( $this->domain !== null ) {
			$options['domain'] = $this->domain;
		}
		if ( $this->secure !== null ) {
			$options['secure'] = $this->secure;
		}
		if ( $this->httpOnly !== null ) {
			$options['httponly'] = $this->httpOnly;
		}
		if ( $this->sameSite !== null ) {
			$options['samesite'] = $this->sameSite;
		}
		return $options;
	}
}
