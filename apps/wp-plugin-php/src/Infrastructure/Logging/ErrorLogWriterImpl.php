<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Logging;

use Baywall\Core\Application\Logging\ErrorLogWriter;

class ErrorLogWriterImpl implements ErrorLogWriter {
	public function write( string $text ): void {
		error_log( $text );
	}
}
