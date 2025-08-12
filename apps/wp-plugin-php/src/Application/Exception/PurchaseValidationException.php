<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Exception;

/** 購入された事実がサーバーで確認できなかった時に発生する例外 */
class PurchaseValidationException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
