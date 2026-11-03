<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

/**
 * プラグインのアンインストールに関連するサービスを提供します
 */
interface PluginTeardownService {
	/** プラグインのアンインストールを実行します */
	public function teardown(): void;
}
