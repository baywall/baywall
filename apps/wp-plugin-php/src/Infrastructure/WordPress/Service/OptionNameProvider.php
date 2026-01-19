<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

class OptionNameProvider {

	private string $prefix;

	public function __construct( PrefixProvider $prefix_provider ) {
		$this->prefix = $prefix_provider->optionKey();
	}

	/**
	 * インストールされたプラグインのバージョン
	 */
	public function pluginVersion(): string {
		return $this->prefix . 'plugin_version';
	}

	/** JWTの秘密鍵 */
	public function jwtSecretKey(): string {
		return $this->prefix . 'jwt_secret_key';
	}

	/** 「特定商取引法に基づく表記」のURL */
	public function sctaUrl(): string {
		return $this->prefix . 'scta_url';
	}
}
