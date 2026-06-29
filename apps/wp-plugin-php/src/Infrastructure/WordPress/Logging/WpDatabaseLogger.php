<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Logging;

use Cornix\Serendipity\Core\Infrastructure\Logging\Handler\SimpleLogger;
use Cornix\Serendipity\Core\Infrastructure\Logging\Logger;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;

class WpDatabaseLogger implements Logger {

	private LogTable $log_table;
	private SimpleLogger $fallback_logger;

	public function __construct( LogTable $log_table, SimpleLogger $fallback_logger ) {
		$this->log_table       = $log_table;
		$this->fallback_logger = $fallback_logger;
	}

	/**
	 * ログを記録します。
	 *
	 * @param LogLevel          $level
	 * @param string|\Throwable $message_or_exception
	 */
	public function log( LogLevel $level, $message_or_exception ): void {
		$original_message = $message_or_exception;

		if ( $message_or_exception instanceof \Throwable ) {
			$message = $message_or_exception->getMessage();
		} else {
			assert( is_string( $message_or_exception ), '[D3A17E8F] Message must be a string or Throwable' );
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
