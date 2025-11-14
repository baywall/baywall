<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\JWT;

use Cornix\Serendipity\Core\Infrastructure\Reimpl\Base64Url\Base64Url;

class JwtCodec {

	private $supported_algorithm_map = array(
		'HS256' => 'sha256',
		// 'HS384' => 'sha384',
		// 'HS512' => 'sha512',
	);

	private Base64Url $base64url;

	public function __construct() {
		$this->base64url = new Base64Url();
	}

	public function encode(
		string $alg,
		array $payload,
		#[\SensitiveParameter]
		string $secret
	): string {
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

		$header_encoded  = $this->base64url->encode( json_encode( $header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
		$payload_encoded = $this->base64url->encode( json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );

		$data = $header_encoded . '.' . $payload_encoded;   // 署名対象文字列

		// 署名をバイナリ形式で生成し、base64URLエンコード(JWT仕様)
		$signature         = hash_hmac( $hash_algorithm, $data, $secret, true );
		$signature_encoded = $this->base64url->encode( $signature );

		return $data . '.' . $signature_encoded;
	}

	public function decode(
		string $jwt,
		#[\SensitiveParameter]
		string $secret
	): array {
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
		$header_json  = $this->base64url->decode( $header_encoded );
		$payload_json = $this->base64url->decode( $payload_encoded );

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
		$expected_signature_encoded = $this->base64url->encode( $expected_signature );

		if ( ! hash_equals( $expected_signature_encoded, $signature_encoded ) ) {
			throw new \InvalidArgumentException( '[857C37AC] JWT signature verification failed.' );
		}

		return $payload;
	}
}
