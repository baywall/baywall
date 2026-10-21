<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Repository;

use Baywall\Core\Application\ValueObject\PluginVersion;

/**
 * インストール済みプラグインバージョンを永続化するリポジトリ
 */
interface InstalledPluginVersionRepository {
	/** インストール済みのプラグインバージョンを取得します */
	public function get(): ?PluginVersion;

	/** インストール済みのプラグインバージョンを更新します */
	public function update( PluginVersion $version ): void;
}
