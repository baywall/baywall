<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Logging;

use Baywall\Core\Application\Logging\ValueObject\LogLevel;

interface Logger {
	/**
	 * ログを記録します。
	 */
	public function log( LogLevel $level, string|\Throwable $message_or_exception ): void;
}
