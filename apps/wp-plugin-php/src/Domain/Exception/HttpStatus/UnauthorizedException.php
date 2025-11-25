<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Exception\HttpStatus;

/**
 * 401 Unauthorized
 *
 * アクセス権がない、または認証に失敗した場合にスローされる例外クラス
 */
class UnauthorizedException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
