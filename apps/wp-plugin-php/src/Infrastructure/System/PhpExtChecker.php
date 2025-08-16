<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\System;

class PhpExtChecker {

	private const REQUIRED_EXTENSIONS = array(
		'bcmath',

		/*
		 * - `gmp`は`simplito/elliptic-php`でrequiredで指定されているが、
		 *   `simplito/elliptic-php`のコード中に`gmp_`関数が使用されていないため、コメントアウト
		 * - `wp-env`の開発環境でも`gmp`が有効でない状態で動作している
		 */
		// 'gmp',

		'iconv',
		'json',
		'mbstring',
	);

	/**
	 * 本アプリケーションに必要なPHP拡張が有効かどうかをチェックし、有効でない場合は例外を投げます。
	 */
	public function checkPhpExtensions(): void {
		foreach ( self::REQUIRED_EXTENSIONS as $extension ) {
			if ( ! extension_loaded( $extension ) ) {
				throw new \RuntimeException( "[3D52931E] PHP extension '{$extension}' is required but not loaded." );
			}
		}
	}
}
