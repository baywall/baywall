<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base;

/**
 * マイグレーションの基底クラス
 */
abstract class MigrationBase {
	/** 適用対象となるプラグインバージョン */
	abstract public function version(): string;

	/** マイグレーションの適用処理 */
	abstract public function up(): void;

	/** マイグレーションの巻き戻し処理 */
	abstract public function down(): void;
}
