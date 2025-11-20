<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Exception\HttpStatus;

/**
 * 402 Payment Required
 *
 * 料金の支払いをするまでリクエストを処理できない状態の時にスローされる例外クラス
 */
class PaymentRequiredException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
