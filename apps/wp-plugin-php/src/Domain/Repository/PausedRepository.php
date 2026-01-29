<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

/**
 * 一時停止状態を取得または設定するクラス
 *
 * - 停止状態の時は請求書発行が行われず、購入処理が制限されます。
 * - サイト所有者がなにらかしらの理由でサイト全体で購入を制限する場合に使用します
 */
interface PausedRepository {

	/** 一時停止状態を取得します */
	public function get(): bool;

	/** 一時停止状態を保存します */
	public function save( bool $paused ): void;
}
