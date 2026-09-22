<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Logging\Handler;

use Baywall\Core\Infrastructure\Logging\Logger;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogLevel;
use DateTimeImmutable;

class SimpleLogger implements Logger {
	/**
	 * ログを記録します。
	 */
	public function log( LogLevel $level, string|\Throwable $message_or_exception ): void {
		$timestamp = ( new DateTimeImmutable() )->setTimestamp( time() )->format( 'Y-m-d H:i:s' );
		if ( $message_or_exception instanceof \Throwable ) {
			error_log(
				sprintf(
					'[%s] [%s] %s: %s in %s on line %d%s',
					$timestamp,
					$level->name(),
					get_class( $message_or_exception ),
					$message_or_exception->getMessage(),
					$message_or_exception->getFile(),
					$message_or_exception->getLine(),
					PHP_EOL . $message_or_exception->getTraceAsString(),
					PHP_EOL
				)
			);
		} else {
			error_log(
				sprintf(
					'[%s] [%s] %s%s',
					$timestamp,
					$level->name(),
					$message_or_exception,
					PHP_EOL
				)
			);
		}
	}
}
