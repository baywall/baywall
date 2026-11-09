<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Logging;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Logging\AppLogLevelRepository;
use Baywall\Core\Application\Logging\ErrorLogWriter;
use Baywall\Core\Application\Logging\ValueObject\LogLevel;
use Baywall\Core\Infrastructure\Logging\ErrorLogFormatter;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;

class WpAppLogService implements AppLogService {
	public function __construct(
		private readonly LogTable $log_table,
		private readonly ErrorLogFormatter $error_log_formatter,
		private readonly AppLogLevelRepository $app_log_level_repository,
		private readonly ErrorLogWriter $error_log_writer
	) {}

	private function log( LogLevel $level, string|\Throwable $message_or_exception ): void {
		try {
			// 現在設定されているログレベルで出力する場合に限り、ログを出力する
			$current_log_level = $this->app_log_level_repository->get();
			if ( ! $current_log_level->allows( $level ) ) {
				return;
			}

			$original_message = $message_or_exception;
			if ( $message_or_exception instanceof \Throwable ) {
				$message = $message_or_exception->getMessage();
			} else {
				$message = $message_or_exception;
			}

			try {
				$this->log_table->insert( $level, $message );
			} catch ( \Throwable $e ) {
				$wrapped = new \RuntimeException( '[863ABB44] Failed to save log to database: ' . $original_message, 0, $e );
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

	public function debug( string|\Throwable $message_or_exception ): void {
		$this->log( LogLevel::debug(), $message_or_exception );
	}

	public function info( string|\Throwable $message_or_exception ): void {
		$this->log( LogLevel::info(), $message_or_exception );
	}

	public function warn( string|\Throwable $message_or_exception ): void {
		$this->log( LogLevel::warn(), $message_or_exception );
	}

	public function error( string|\Throwable $message_or_exception ): void {
		$this->log( LogLevel::error(), $message_or_exception );
	}
}
