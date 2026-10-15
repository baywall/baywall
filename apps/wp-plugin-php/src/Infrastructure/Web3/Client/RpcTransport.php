<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

/** JSON-RPC リクエストの HTTP POST 送信を担うトランスポート */
interface RpcTransport {

	/**
	 * HTTP POST でボディを送信します。
	 *
	 * @param string               $url     送信先 URL
	 * @param string               $body    リクエストボディ
	 * @param array<string,string> $headers リクエストヘッダ
	 * @param int                  $timeout タイムアウト秒数
	 * @return array{ status: int, body: string } HTTP ステータスコードとレスポンスボディ
	 * @throws \RuntimeException 送信に失敗した場合
	 */
	public function post( string $url, string $body, array $headers, int $timeout ): array;
}
