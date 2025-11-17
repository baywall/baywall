<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Exception;

/**
 * 無効な請求書トークンが使用された場合にスローされる例外
 */
class InvalidInvoiceTokenException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
