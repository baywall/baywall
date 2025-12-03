<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Exception\HttpStatus;

/**
 * 400 Bad Request
 *
 * リクエストが不正な場合にスローされる例外クラス
 */
class BadRequestException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
