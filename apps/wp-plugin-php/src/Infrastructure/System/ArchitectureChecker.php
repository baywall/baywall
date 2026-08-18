<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\System;

class ArchitectureChecker {
	/**
	 * 64ビットのPHP環境であることを確認します。
	 *
	 * @param int $php_int_size PHPの整数サイズ(バイト数)。通常は `PHP_INT_SIZE` を渡します。
	 */
	public function checkIs64bit( int $php_int_size ): void {
		if ( $php_int_size !== 8 ) {
			throw new \RuntimeException( '[D55151B4] This application requires a 64-bit PHP environment.' );
		}
	}
}
