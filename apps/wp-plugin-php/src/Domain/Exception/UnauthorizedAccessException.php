<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Exception;

/**
 * 認証されていないユーザーがアクセスした場合にスローされる例外
 */
class UnauthorizedAccessException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
