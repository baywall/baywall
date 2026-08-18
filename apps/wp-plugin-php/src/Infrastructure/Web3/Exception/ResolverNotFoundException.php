<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Exception;

/** ENS 名に対応する Resolver が未設定の場合に発生する例外 */
class ResolverNotFoundException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
