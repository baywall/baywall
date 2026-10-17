<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Exception;

/**
 * 支払いが完了・確認できていない場合（未購入、決済情報なし）にスローされる例外クラス
 */
class PaymentNotCompletedException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
