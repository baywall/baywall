<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

/** PHP ストリーム（http / https ラッパー）で HTTP POST する `RpcTransport` の既定実装 */
class HttpRpcTransport implements RpcTransport {
	/** @inheritDoc */
	public function post( string $url, string $body, array $headers, int $timeout ): array {
		return HttpStreamRequest::request( $url, 'POST', $headers, $body, $timeout );
	}

	/**
	 * HTTP レスポンスヘッダの配列から HTTP ステータスコードを抽出します。
	 *
	 * @param string[] $response_headers HTTPレスポンスヘッダの配列
	 * @return int HTTP ステータスコード
	 * @throws \RuntimeException ステータス行が見つからない場合
	 */
	public static function parseStatusCode( array $response_headers ): int {
		return HttpStreamRequest::parseStatusCode( $response_headers );
	}
}
