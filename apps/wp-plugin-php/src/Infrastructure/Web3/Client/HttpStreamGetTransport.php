<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

/** PHP ストリーム（http / https ラッパー）で HTTP GET する `HttpGetTransport` の既定実装 */
class HttpStreamGetTransport implements HttpGetTransport {
	/** @inheritDoc */
	public function get( string $url, int $timeout ): array {
		return HttpStreamRequest::request( $url, 'GET', array(), null, $timeout );
	}
}
