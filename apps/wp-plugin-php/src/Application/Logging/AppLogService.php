<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Logging;

interface AppLogService {
	public function debug( string|\Throwable $message_or_exception ): void;

	public function info( string|\Throwable $message_or_exception ): void;

	public function warn( string|\Throwable $message_or_exception ): void;

	public function error( string|\Throwable $message_or_exception ): void;
}
