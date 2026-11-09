<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

/** ロック機構(排他制御)を提供するインターフェース */
interface LockService {
	/**
	 * ロックを取得します。
	 *
	 * @param string $lock_name ロックの名前
	 * @param int    $timeout_sec ロックのタイムアウト時間（秒）
	 * @return bool ロックの取得に成功した場合はtrue
	 */
	public function acquire( string $lock_name, int $timeout_sec ): bool;

	/**
	 * ロックを解放します。
	 *
	 * @param string $lock_name ロックの名前
	 * @return bool ロックの解放に成功した場合はtrue
	 */
	public function release( string $lock_name ): bool;
}
