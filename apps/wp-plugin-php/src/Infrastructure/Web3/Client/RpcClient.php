<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Exception\RpcException;

/** JSON-RPC 2.0 のリクエストを1件送信し、`result` を返すクラス */
class RpcClient {
	public function __construct( private readonly RpcUrl $rpc_url ) {}

	/**
	 * JSON-RPC 2.0 のリクエストを送信し、応答の `result` を返します。
	 *
	 * @param string  $method RPC メソッド名
	 * @param mixed[] $params
	 * @return mixed 応答の `result`。整形は呼び出し側の責務
	 * @throws RpcException 送信失敗・HTTPエラー・JSONパース失敗・JSON-RPCエラー・`result` 欠落の場合
	 */
	public function request( string $method, array $params = array() ): mixed {
		$body = wp_json_encode(
			array(
				'jsonrpc' => '2.0',
				'id'      => self::newRequestId(),
				'method'  => $method,
				'params'  => $params,
			)
		);

		$response = wp_remote_post(
			$this->rpc_url->value(),
			array(
				'timeout' => Config::BLOCKCHAIN_REQUEST_TIMEOUT,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new RpcException( '[8A5C1D3F] JSON-RPC request failed: ' . $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			throw new RpcException( "[5E9F2B7C] JSON-RPC endpoint returned HTTP status {$status_code}." );
		}

		try {
			$decoded = json_decode( wp_remote_retrieve_body( $response ), false, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			throw new RpcException( '[F1D2B3C4] JSON-RPC response is not valid JSON.', 0, $e );
		}

		if ( isset( $decoded->error ) ) {
			// error.code / error.message が無い応答に備え、フォールバックで RpcException に統一する
			$code    = (int) ( $decoded->error->code ?? 0 );
			$message = (string) ( $decoded->error->message ?? '' );
			throw new RpcException( "[B4A8E6D0] JSON-RPC error (code: {$code}): {$message}", $code );
		}

		// property_exists() は非オブジェクトで TypeError になるため is_object() を先に判定する
		if ( ! is_object( $decoded ) || ! property_exists( $decoded, 'result' ) ) {
			throw new RpcException( '[A9B1F38C] JSON-RPC response has no result.' );
		}

		return $decoded->result;
	}

	/** AGENTS.md「ID Generation」に従い `[A-F0-9]{8}` 形式のリクエストIDを採番します */
	private static function newRequestId(): string {
		return strtoupper( bin2hex( random_bytes( 4 ) ) );
	}
}
