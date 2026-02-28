<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Reimpl\Base64Url;

/**
 * Base64URLエンコード/デコードクラス
 *
 * - JWTで使用
 *
 * @see https://ja.wikipedia.org/wiki/Base64#%E5%A4%89%E5%BD%A2%E7%89%88
 */
class Base64Url {

	public function encode( string $data ): string {
		// `+ => -`, `/ => _`, パディング削除
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	public function decode( string $data ): string {
		// パディング追加
		$remainder = strlen( $data ) % 4;
		if ( $remainder > 0 ) {
			$data .= str_repeat( '=', 4 - $remainder );
		}
		// `- => +`, `_ => /`
		return base64_decode( strtr( $data, '-_', '+/' ) );
	}
}
