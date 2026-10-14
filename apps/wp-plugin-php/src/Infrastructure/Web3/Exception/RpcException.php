<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Exception;

/** JSON-RPC リクエストの失敗時に発生する例外 */
class RpcException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
