<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

/** プラグインの自動アップデート状態を判定するサービスを提供します */
interface PluginAutoUpdateStateProvider {
	/** 自プラグインが自動アップデートされる状態にあるかを取得します */
	public function isAutoUpdateEnabled(): bool;
}
