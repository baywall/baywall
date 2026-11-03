<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

/** プラグインの無効化を行うサービスを提供します */
interface PluginDeactivationService {
	/** プラグインを無効化します */
	public function deactivatePlugin(): void;
}
