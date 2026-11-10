<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration;

/**
 * マイグレーションを表すインターフェース
 */
interface Migration {
	/** 適用対象となるプラグインバージョン */
	public function version(): string;

	/** マイグレーションの適用処理 */
	public function up(): void;

	/** マイグレーションの巻き戻し処理 */
	public function down(): void;
}
