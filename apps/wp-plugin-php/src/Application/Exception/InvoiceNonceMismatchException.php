<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Exception;

/** Invoice に紐づく nonce が期待する値と一致しなかった時に発生する例外 */
class InvoiceNonceMismatchException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
