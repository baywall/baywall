<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Exception;

/**
 * 操作の検証に失敗した場合にスローされる例外クラス
 *
 * 対象: 必須項目の欠落・値の不正・署名検証失敗・nonce 不正・前提整合性の不一致・重複購入チェックの失敗。
 * 重複購入は「操作の実行前検証が通らず受理できない」状態の一種であり、DuplicatePurchaseException は本クラスを継承する。
 */
class ValidationFailedException extends \RuntimeException {
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $message = '', int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
