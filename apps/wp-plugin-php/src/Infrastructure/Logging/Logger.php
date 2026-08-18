<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Logging;

use Baywall\Core\Infrastructure\Logging\ValueObject\LogLevel;

interface Logger {
	/**
	 * ログを記録します。
	 *
	 * @param LogLevel          $level
	 * @param string|\Throwable $message_or_exception
	 */
	public function log( LogLevel $level, $message_or_exception ): void;
}
