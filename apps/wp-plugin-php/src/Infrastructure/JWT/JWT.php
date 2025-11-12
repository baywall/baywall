<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\JWT;

class JWT {

	private $supported_algorithm_map = array(
		'HS256' => 'sha256',
		// 'HS384' => 'sha384',
		// 'HS512' => 'sha512',
	);

	public function encode( string $alg, array $payload, string $secret ): string {
		if ( $secret === '' ) {
			// $secretが空文字の場合は例外をスロー
			throw new \InvalidArgumentException( '[9A2D9CD6] Secret key must not be empty.' );
		}

		$header = array(
			'alg' => $alg,
			'typ' => 'JWT',
		);

		$hash_algorithm = $this->supported_algorithm_map[ $alg ] ?? null;
		if ( $hash_algorithm === null ) {
			throw new \InvalidArgumentException( "[8812C526] Unsupported JWT algorithm: {$alg}" );
		}

		$header_encoded  = $this->jwtBase64UrlEncode( json_encode( $header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
		$payload_encoded = $this->jwtBase64UrlEncode( json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );

		$data = $header_encoded . '.' . $payload_encoded;   // 署名対象文字列

		// 署名をバイナリ形式で生成し、base64URLエンコード(JWT仕様)
		$signature         = hash_hmac( $hash_algorithm, $data, $secret, true );
		$signature_encoded = $this->jwtBase64UrlEncode( $signature );

		return $data . '.' . $signature_encoded;
	}

	public function decode( string $jwt, string $secret ): array {
		$parts = explode( '.', $jwt );
		if ( count( $parts ) !== 3 ) {
			throw new \InvalidArgumentException( '[EC8F3FD8] Invalid JWT format.' );
		}
		if ( $secret === '' ) {
			// $secretが空文字の場合は例外をスロー
			throw new \InvalidArgumentException( '[4E199DB3] Secret key must not be empty.' );
		}

		[$header_encoded, $payload_encoded, $signature_encoded] = $parts;

		// ヘッダーとペイロードのJSON文字列をデコード
		$header_json  = $this->jwtBase64UrlDecode( $header_encoded );
		$payload_json = $this->jwtBase64UrlDecode( $payload_encoded );

		// 連想配列に変換
		$header  = json_decode( $header_json, true );
		$payload = json_decode( $payload_json, true );

		if ( ! is_array( $header ) || ! isset( $header['alg'] ) ) {
			throw new \InvalidArgumentException( "[97391C4B] JWT header missing 'alg' field." );
		} elseif ( ! is_array( $payload ) ) {
			throw new \InvalidArgumentException( '[400F78C8] Invalid JWT payload.' );
		}
		/** @var array<string, mixed> $header */
		/** @var array<string, mixed> $payload */

		$alg            = $header['alg'];
		$hash_algorithm = $this->supported_algorithm_map[ $alg ] ?? null;
		if ( $hash_algorithm === null ) {
			throw new \InvalidArgumentException( "[F8F5FAEB] Unsupported JWT algorithm: {$alg}" );
		}

		// 署名検証
		$data                       = $header_encoded . '.' . $payload_encoded;   // 署名対象文字列
		$expected_signature         = hash_hmac( $hash_algorithm, $data, $secret, true );
		$expected_signature_encoded = $this->jwtBase64UrlEncode( $expected_signature );

		if ( ! hash_equals( $expected_signature_encoded, $signature_encoded ) ) {
			throw new \InvalidArgumentException( '[857C37AC] JWT signature verification failed.' );
		}

		return $payload;
	}

	private function jwtBase64UrlEncode( string $data ): string {
		// `+ => -`, `/ => _`, パディング削除
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}
	private function jwtBase64UrlDecode( string $data ): string {
		// パディング追加
		$remainder = strlen( $data ) % 4;
		if ( $remainder > 0 ) {
			$data .= str_repeat( '=', 4 - $remainder );
		}
		// `- => +`, `_ => /`
		return base64_decode( strtr( $data, '-_', '+/' ) );
	}
}
