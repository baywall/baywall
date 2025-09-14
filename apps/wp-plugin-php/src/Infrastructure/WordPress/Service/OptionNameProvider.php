<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

class OptionNameProvider {

	private string $prefix;

	public function __construct( PrefixProvider $prefixProvider ) {
		$this->prefix = $prefixProvider->optionKey();
	}

	/**
	 * インストールされたプラグインのバージョン
	 */
	public function pluginVersion(): string {
		return $this->prefix . 'plugin_version';
	}
}
