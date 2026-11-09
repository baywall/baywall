<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Logging;

interface ErrorLogWriter {
	/** 整形済みテキストを error_log に出力します。 */
	public function write( string $text ): void;
}
