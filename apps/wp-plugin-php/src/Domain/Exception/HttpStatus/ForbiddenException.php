<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Exception\HttpStatus;

/**
 * 403 Forbidden
 *
 * アクセス権がない場合にスローされる例外クラス
 */
class ForbiddenException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
