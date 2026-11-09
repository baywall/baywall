<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\Exception\LockAcquisitionException;
use Baywall\Core\Application\Port\LockService;

class LockRunner {
	public function __construct( private readonly LockService $lock_service ) {}

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
	public function withLock( string $lock_name, callable $callback, int $timeout_sec = 0 ) {
		if ( $this->lock_service->acquire( $lock_name, $timeout_sec ) ) {
			try {
				return $callback();
			} finally {
				$success = $this->lock_service->release( $lock_name );
				assert( $success, "[20A49B81] Failed to release lock: {$lock_name}" );
			}
		}
		throw new LockAcquisitionException( "[B775CC71] Failed to acquire lock: {$lock_name}" );
	}
}
