<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\Service\CookieNameProvider;

class WpCookieNameProvider implements CookieNameProvider {

	private const SECURE_PREFIX = '__Secure-';

	private WordPressPropertyProvider $wp_property;

	public function __construct( WordPressPropertyProvider $wp_property ) {
		$this->wp_property = $wp_property;
	}

	/** @inheritdoc */
	public function accessToken(): string {
		return $this->resolve( WpConfig::COOKIE_NAME_ACCESS_TOKEN );
	}

	/** @inheritdoc */
	public function refreshToken(): string {
		return $this->resolve( WpConfig::COOKIE_NAME_REFRESH_TOKEN );
	}

	/** @inheritdoc */
	public function invoiceToken(): string {
		return $this->resolve( WpConfig::COOKIE_NAME_INVOICE_TOKEN );
	}

	private function resolve( string $base_cookie_name ): string {
		if ( $this->wp_property->getEnvironmentType() === 'local' ) {
			// テスト環境の場合は、セキュアプレフィックスなしのクッキー名を返す
			return $base_cookie_name;
		} else {
			assert( strpos( $base_cookie_name, self::SECURE_PREFIX ) !== 0, '[2472BAE7] Base cookie name should not start with secure prefix.' );
			return self::SECURE_PREFIX . $base_cookie_name;
		}
	}
}
