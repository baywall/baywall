<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Exception;

/**
 * 十分な確認が得られていない場合にスローされる例外
 *
 * 支払いトランザクションが含まれるブロックから、必要な確認数に達していない場合に発生します
 */
class InsufficientConfirmationsException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
