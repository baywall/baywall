<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\System;

use RuntimeException;

class ArchitectureChecker {
	/** 64ビットのPHP環境であることを確認します。 */
	public function checkIs64bit(): void {
		if ( PHP_INT_SIZE !== 8 ) {
			throw new \RuntimeException( '[D55151B4] This application requires a 64-bit PHP environment.' );
		}
	}
}
