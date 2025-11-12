<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\JWT;

/**
 * JWT仕様に準拠したBase64URLエンコード/デコードクラス
 */
class JwtBase64Url {

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
