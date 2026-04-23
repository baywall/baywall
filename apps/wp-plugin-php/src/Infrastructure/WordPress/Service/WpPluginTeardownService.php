<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\PluginTeardownService;

class WpPluginTeardownService implements PluginTeardownService {
	public function teardown(): void {
		// TODO: プラグインのアンインストール処理を実装
	}
}
