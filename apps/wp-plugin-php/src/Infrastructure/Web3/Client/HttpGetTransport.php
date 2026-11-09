<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

/** HTTP GET 送信を担うトランスポート */
interface HttpGetTransport {
	/**
	 * HTTP GET で取得します。
	 *
	 * @param string $url     取得先 URL
	 * @param int    $timeout タイムアウト秒数
	 * @return array{ status: int, body: string } HTTP ステータスコードとレスポンスボディ
	 * @throws \RuntimeException 送信に失敗した場合
	 */
	public function get( string $url, int $timeout ): array;
}
