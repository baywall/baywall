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

		// $http_response_header は PHP 9.0 で削除予定（PHP 8.5 以降で非推奨）。
		// また、http_get_last_response_headers 関数は PHP 8.4 以降で使用可。
		// PHP 8.2, 8.3 環境でも動作するように関数チェックを行ってから呼び出す。
		// @see https://www.php.net/manual/en/reserved.variables.httpresponseheader.php
		// @see https://php-errors.readthedocs.io/en/latest/messages/the-predefined-locally-scoped-$http_response_header-variable-is-deprecated,.html
		$response_headers = function_exists( 'http_get_last_response_headers' )
			? ( http_get_last_response_headers() ?? array() )
			: ( $http_response_header ?? array() );

		return array(
			'status' => self::parseStatusCode( $response_headers ),
			'body'   => $response_body,
		);
	}

	/**
	 * HTTP レスポンスヘッダの配列から HTTP ステータスコードを抽出します。
	 *
	 * リダイレクト追随時は最終ブロックが実応答のため、最後のステータス行を採用します。
	 *
	 * @param string[] $response_headers HTTPレスポンスヘッダの配列
	 * @return int HTTP ステータスコード
	 * @throws \RuntimeException ステータス行が見つからない場合
	 */
	public static function parseStatusCode( array $response_headers ): int {
		for ( $index = count( $response_headers ) - 1; $index >= 0; $index-- ) {
			if ( preg_match( '#^HTTP/\S+\s+(\d{3})#', $response_headers[ $index ], $matches ) ) {
				return (int) $matches[1];
			}
		}

		throw new \RuntimeException( 'HTTP status line not found in response headers.' );
	}
}
