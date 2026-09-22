<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Logging;

use Baywall\Core\Infrastructure\Logging\Logger;
use Baywall\Core\Infrastructure\Logging\LogLevelRepository;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogLevel;

class AppLogger {
	public function __construct(
		private readonly Logger $logger,
		private readonly LogLevelRepository $log_level_repository
	) {}

	private function log( LogLevel $level, string|\Throwable $message_or_exception ): void {
		try {
			$current_log_level = $this->log_level_repository->get( LogCategory::app() );
			if ( $current_log_level->allows( $level ) ) {
				// 現在設定されているログレベルで出力する場合に限り、ログを出力する
				$this->logger->log( $level, $message_or_exception );
			}
		} catch ( \Throwable $e ) {
			// ログの無限ループに陥らないようにerror_logで出力するだけ
			try {
				error_log( (string) $message_or_exception );
				error_log( (string) $e );
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
