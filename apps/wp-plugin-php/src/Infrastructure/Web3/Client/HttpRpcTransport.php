<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

/** PHP ストリーム（http / https ラッパー）で HTTP POST する `RpcTransport` の既定実装 */
class HttpRpcTransport implements RpcTransport {

	/** @inheritDoc */
	public function post( string $url, string $body, array $headers, int $timeout ): array {
		$header_lines = array();
		foreach ( $headers as $name => $value ) {
			$header_lines[] = "{$name}: {$value}";
		}

		$context = stream_context_create(
			array(
				'http' => array(
					'method'        => 'POST',
					'header'        => implode( "\r\n", $header_lines ),
					'content'       => $body,
					'timeout'       => $timeout,
					// 200 以外でもステータスとボディを読み取るため
					'ignore_errors' => true,
				),
			)
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.PHP.NoSilencedErrors -- HTTP送信の失敗は戻り値で検知して例外に変換するため
		$response_body = @file_get_contents( $url, false, $context );
		if ( false === $response_body ) {
			$error = error_get_last();
			// 警告メッセージは `file_get_contents(<URL>): <失敗理由>` 形式で送信先URL（APIキー等を含み得る）を含むため、
			// URL 部が残らないよう最長一致でプレフィックスを除去し失敗理由のみを載せる
			$reason = preg_replace( '#^file_get_contents\(.*\): #', '', $error['message'] ?? '' ) ?? '';
			if ( '' === $reason ) {
				$reason = 'HTTP request failed.';
			}
			throw new \RuntimeException( $reason );
		}

		return array(
			'status' => self::parseStatusCode( $http_response_header ?? array() ),
			'body'   => $response_body,
		);
	}

	/**
	 * `$http_response_header` から HTTP ステータスコードを抽出します。
	 *
	 * リダイレクト追随時は最終ブロックが実応答のため、最後のステータス行を採用します。
	 *
	 * @param string[] $http_response_header HTTPレスポンスヘッダの配列
	 * @return int HTTP ステータスコード
	 * @throws \RuntimeException ステータス行が見つからない場合
	 */
	public static function parseStatusCode( array $http_response_header ): int {
		for ( $index = count( $http_response_header ) - 1; $index >= 0; $index-- ) {
			if ( preg_match( '#^HTTP/\S+\s+(\d{3})#', $http_response_header[ $index ], $matches ) ) {
				return (int) $matches[1];
			}
		}

		throw new \RuntimeException( 'HTTP status line not found in response headers.' );
	}
}
