<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

interface PluginInfoProvider {
	/**
	 * プラグインのバージョンを取得します。
	 */
	public function version(): string;
}
