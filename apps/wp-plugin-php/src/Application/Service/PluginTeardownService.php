<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

/**
 * プラグインのアンインストールに関連するサービスを提供します
 */
interface PluginTeardownService {
	/** プラグインのアンインストールを実行します */
	public function teardown(): void;
}
