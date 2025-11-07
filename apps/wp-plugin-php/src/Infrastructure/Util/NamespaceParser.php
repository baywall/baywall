<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Util;

/** PHPファイルを解析して名前空間を取得するクラス */
class NamespaceParser {

	public function get( string $file_path ): string {
		$contents = file_get_contents( $file_path );
		if ( $contents === false ) {
			throw new \RuntimeException( "[BFBD62E7] Failed to read file: {$file_path}" );
		}

		$namespace       = '';
		$namespace_found = false;
		foreach ( token_get_all( $contents ) as $token ) {
			if ( is_array( $token ) && $token[0] === T_NAMESPACE ) {
				$namespace_found = true;
			}
			if ( $namespace_found ) {
				if ( is_array( $token ) ) {
					// PHP8.0以降の新しいトークンタイプに対応
					if ( defined( 'T_NAME_QUALIFIED' ) && $token[0] === T_NAME_QUALIFIED ) {
						$namespace .= $token[1];
					} elseif ( defined( 'T_NAME_FULLY_QUALIFIED' ) && $token[0] === T_NAME_FULLY_QUALIFIED ) {
						$namespace .= $token[1];
					} elseif ( defined( 'T_NAME_RELATIVE' ) && $token[0] === T_NAME_RELATIVE ) {
						$namespace .= $token[1];
					} elseif ( in_array( $token[0], array( T_STRING, T_NS_SEPARATOR ), true ) ) {
						// PHP7.4以前の互換性を保持
						$namespace .= $token[1];
					}
				} elseif ( $token === ';' || $token === '{' ) {
					$namespace_found = false;
				}
			}
		}

		return $namespace;
	}
}
