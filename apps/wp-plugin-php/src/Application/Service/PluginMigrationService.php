<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

/**
 * プラグインのマイグレーションに関連するサービスを提供します
 */
interface PluginMigrationService {
	/** プラグインのマイグレーションを実行します */
	public function migrate(): void;

	/** マイグレーションが必要かどうかを取得します */
	public function required(): bool;
}
