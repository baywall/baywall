<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Logging;

interface AuditLogService {
	public function info( string $action, string|\Throwable $message_or_exception ): void;

	public function warn( string $action, string|\Throwable $message_or_exception ): void;

	public function error( string $action, string|\Throwable $message_or_exception ): void;
}
