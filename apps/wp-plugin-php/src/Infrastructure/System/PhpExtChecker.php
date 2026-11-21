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
	 *   `simplito/elliptic-php`のcomposer `require: ext-gmp`は過剰な宣言(コード上`gmp_`の直接呼び出しなし)で、
	 *   composer install時にのみgmpを要求する点がランタイム要件と不整合。メタデータ自体は変更しない。
	 *   また、通常のビルドで有効となる bcmath と異なり、`--with-gmp` オプションで明示的にコンパイルしないといけないため、
	 *   初期セットアップ時に有効になっていないサーバーが多数存在（あとから有効にできるサーバーや、そもそも有効にできないサーバーが混在）。
	 *   そのため、`gmp` を必須とする依存ライブラリは原則導入不可。
	 *
	 * チェック対象外の拡張:
	 * - `json`:PHP >= 8.0で常駐(本プラグインはPHP >= 8.2前提)のためチェック不要
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
