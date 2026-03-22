<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Exception\LockAcquisitionException;

/** ロック機構(排他制御)を提供する抽象クラス */
abstract class LockService {
	/**
	 * ロックを取得します。
	 *
	 * @param string $lock_name ロックの名前
	 * @param int    $timeout_sec ロックのタイムアウト時間（秒）
	 * @return bool ロックの取得に成功した場合はtrue
	 */
	abstract public function acquire( string $lock_name, int $timeout_sec ): bool;

	/**
	 * ロックを解放します。
	 *
	 * @param string $lock_name ロックの名前
	 * @return bool ロックの解放に成功した場合はtrue
	 */
	abstract public function release( string $lock_name ): bool;

	/**
	 * ロックを取得して、コールバック関数を実行します。
	 *
	 * @template T
	 * @param string       $lock_name ロックの名前
	 * @param callable():T $callback ロックを取得した後に実行するコールバック関数
	 * @param int          $timeout_sec ロックのタイムアウト時間（秒）
	 * @return T コールバック関数の実行結果
	 * @throws LockAcquisitionException ロックの取得に失敗した場合にスローされる例外
	 */
	final public function withLock( string $lock_name, callable $callback, int $timeout_sec = 0 ) {
		if ( $this->acquire( $lock_name, $timeout_sec ) ) {
			try {
				return $callback();
			} finally {
				$success = $this->release( $lock_name );
				assert( $success, "[20A49B81] Failed to release lock: {$lock_name}" );
			}
		}
		throw new LockAcquisitionException( "[B775CC71] Failed to acquire lock: {$lock_name}" );
	}
}
