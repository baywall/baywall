<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\System;

class PhpExtChecker {

	/**
	 * ランタイムに必須のPHP拡張。
	 *
	 * - `bcmath`:`Amount`/`Hex`で直接使用
	 * - `mbstring`:`Strings`で直接使用
	 *
	 * 任意扱いの拡張:
	 * - `gmp`:`simplito/bigint-wrapper-php`がBCMathにフォールバックするためランタイムに不要。
	 *   `simplito/elliptic-php`のcomposer `require: ext-gmp`は過大宣言(コード上`gmp_`直接呼出しなし)で、
	 *   composer install時にのみgmpを要求する点がランタイム要件と不整合。メタデータ自体は変更しない。
	 *
	 * チェック対象外の拡張:
	 * - `json`:PHP >= 8.0で常駐(本プラグインはPHP >= 8.1前提)のためチェック不要
	 * - `iconv`:`src/`に直接使用がなく、必須とする`mbstring`により`symfony/polyfill-mbstring`も発火しない。
	 *   composerレベルの`ext-iconv`要求は存続するがランタイム要件ではない
	 */
	private const REQUIRED_EXTENSIONS = array( 'bcmath', 'mbstring' );

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
