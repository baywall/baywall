<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

/**
 * プラグインバージョンを表す値オブジェクト
 */
class PluginVersion extends SemVer implements \Stringable {

	public function __construct( string $plugin_version ) {
		parent::__construct( $plugin_version );
	}

	public static function from( string $plugin_version ): self {
		return new self( $plugin_version );
	}
}
