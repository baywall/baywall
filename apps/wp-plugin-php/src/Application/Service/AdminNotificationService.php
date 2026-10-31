<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

/**
 * 管理者へのメール通知を提供する最小の抽象。
 *
 * 送信はbest-effortとし、送信失敗時に例外を送出しない。
 */
interface AdminNotificationService {
	public function notify( string $subject, string $message ): void;
}
