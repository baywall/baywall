<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\OptionGateway;

use Cornix\Serendipity\Core\Lib\Option\StringOption;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\OptionNameProvider;

/**
 * プラグインバージョンを取得または保存するクラス
 * プラグインがインストールされた時にこの値が書き込まれます。
 */
class PluginVersionOption extends StringOption {
	public function __construct( OptionNameProvider $optionNameProvider ) {
		parent::__construct( $optionNameProvider->pluginVersion() );
	}
}
