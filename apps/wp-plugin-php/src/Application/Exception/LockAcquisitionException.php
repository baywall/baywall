<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Exception;

/** ロックの取得に失敗した場合にスローされる例外 */
class LockAcquisitionException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
