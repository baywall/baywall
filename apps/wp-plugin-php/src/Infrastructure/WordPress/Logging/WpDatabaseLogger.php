<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Logging;

use Baywall\Core\Infrastructure\Logging\Handler\SimpleLogger;
use Baywall\Core\Infrastructure\Logging\Logger;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;

class WpDatabaseLogger implements Logger {


	public function __construct(
		private readonly LogTable $log_table,
		private readonly SimpleLogger $fallback_logger
	) {}

	/**
	 * ログを記録します。
	 */
	public function log( LogLevel $level, string|\Throwable $message_or_exception ): void {
		$original_message = $message_or_exception;

		if ( $message_or_exception instanceof \Throwable ) {
			$message = $message_or_exception->getMessage();
		} else {
			$message = $message_or_exception;
		}

		try {
			$this->log_table->insert( $level, LogCategory::app(), $message );
		} catch ( \Throwable $e ) {
			$wrapped = new \RuntimeException( 'Failed to save log to database: ' . $original_message, 0, $e );
			$this->fallback_logger->log( $level, $wrapped );
		}
	}
}
