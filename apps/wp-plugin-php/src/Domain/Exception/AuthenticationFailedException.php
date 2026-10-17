<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Exception;

/**
 * 認証情報（アクセス・リフレッシュ・請求書トークン、nonce）の欠落・無効により認証に失敗した場合にスローされる例外クラス
 */
class AuthenticationFailedException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
