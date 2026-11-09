<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Logging;

use Baywall\Core\Application\Logging\AuditLogService;
use Baywall\Core\Application\Logging\ErrorLogWriter;
use Baywall\Core\Application\Logging\ValueObject\LogLevel;
use Baywall\Core\Infrastructure\Logging\ErrorLogFormatter;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\AuditLogTable;

class WpAuditLogService implements AuditLogService {
	public function __construct(
		private readonly AuditLogTable $audit_log_table,
		private readonly ErrorLogFormatter $error_log_formatter,
		private readonly ErrorLogWriter $error_log_writer
	) {}

	private function log( LogLevel $level, string $action, string|\Throwable $message_or_exception ): void {
		try {
			$original_message = $message_or_exception;
			if ( $message_or_exception instanceof \Throwable ) {
				$message = $message_or_exception->getMessage();
			} else {
				$message = $message_or_exception;
			}

			try {
				$this->audit_log_table->insert( $level, get_current_user_id(), $action, $message );
			} catch ( \Throwable $e ) {
				$wrapped = new \RuntimeException( '[EEC56B1F] Failed to save audit log to database: ' . $original_message, 0, $e );
				$this->error_log_writer->write( $this->error_log_formatter->toErrorLogText( $level, $wrapped ) );
			}
		} catch ( \Throwable $e ) {
			// ログの無限ループに陥らないようにerror_logで出力するだけ
			try {
				$this->error_log_writer->write( (string) $message_or_exception );
				$this->error_log_writer->write( (string) $e );
			} catch ( \Throwable $e2 ) {
				// Do nothing
			}
		}
	}

	public function info( string $action, string|\Throwable $message_or_exception ): void {
		$this->log( LogLevel::info(), $action, $message_or_exception );
	}

	public function warn( string $action, string|\Throwable $message_or_exception ): void {
		$this->log( LogLevel::warn(), $action, $message_or_exception );
	}

	public function error( string $action, string|\Throwable $message_or_exception ): void {
		$this->log( LogLevel::error(), $action, $message_or_exception );
	}
}
