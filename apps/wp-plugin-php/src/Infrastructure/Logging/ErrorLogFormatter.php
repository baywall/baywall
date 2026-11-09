<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Logging;

use Baywall\Core\Application\Logging\ValueObject\LogLevel;
use Baywall\Core\Application\Port\Clock;
use DateTimeImmutable;

class ErrorLogFormatter {
	public function __construct( private readonly Clock $clock ) {}

	/** error_log 出力用のテキストに整形します（出力自体は ErrorLogWriter が行う）。 */
	public function toErrorLogText( LogLevel $level, string|\Throwable $message_or_exception ): string {
		$timestamp = ( new DateTimeImmutable() )->setTimestamp( $this->clock->now()->value() )->format( 'Y-m-d H:i:s' );
		if ( $message_or_exception instanceof \Throwable ) {
			return sprintf(
				'[%s] [%s] %s: %s in %s on line %d%s%s',
				$timestamp,
				$level->name(),
				get_class( $message_or_exception ),
				$message_or_exception->getMessage(),
				$message_or_exception->getFile(),
				$message_or_exception->getLine(),
				PHP_EOL . $message_or_exception->getTraceAsString(),
				PHP_EOL
			);
		}
		return sprintf(
			'[%s] [%s] %s%s',
			$timestamp,
			$level->name(),
			$message_or_exception,
			PHP_EOL
		);
	}
}
