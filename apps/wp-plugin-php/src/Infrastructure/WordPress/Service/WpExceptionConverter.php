<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Domain\Exception\AuthenticationFailedException;
use Baywall\Core\Domain\Exception\PaymentNotCompletedException;
use Baywall\Core\Domain\Exception\PermissionDeniedException;
use Baywall\Core\Domain\Exception\ValidationFailedException;
use InvalidArgumentException;
use WP_Error;
use WP_REST_Response;

/** WordPressの例外変換サービス */
class WpExceptionConverter {

	/** 例外をビジネス分類へ変換します。未分類の例外は INTERNAL_ERROR になります */
	public function categoryOf( \Throwable $e ): ExceptionCategory {
		return match ( true ) {
			$e instanceof ValidationFailedException => ExceptionCategory::VALIDATION_FAILED,
			$e instanceof AuthenticationFailedException => ExceptionCategory::AUTHENTICATION_FAILED,
			$e instanceof PaymentNotCompletedException => ExceptionCategory::PAYMENT_NOT_COMPLETED,
			$e instanceof PermissionDeniedException => ExceptionCategory::PERMISSION_DENIED,
			default => ExceptionCategory::INTERNAL_ERROR,
		};
	}

	public function toWpError( \Throwable $e ): WP_Error {
		// WP_Error 型の場合はそのまま返す
		if ( $e instanceof WP_Error ) {
			assert( false, '[7D72E2C8] ' . var_export( $e, true ) ); // 通常ここは通らない
			return $e;
		}

		// 独自例外クラスを WP_Error 型に変換して返す
		$category = $this->categoryOf( $e );
		return new WP_Error(
			$category->wpErrorCode(),
			$category->clientMessage(),
			array( 'status' => $category->httpStatus() )
		);
	}

	/** 内部で発生した例外を WP_REST_Response 型に変換します */
	public function toWpResponse( \Throwable $e ): WP_REST_Response {
		// WP_REST_Response 型の場合はそのまま返す
		if ( $e instanceof WP_REST_Response ) {
			assert( false, '[6C1C2987] ' . var_export( $e, true ) ); // 通常ここは通らない
			return $e;
		}

		// 独自例外クラスを WP_REST_Response 型に変換して返す
		// - メッセージは抽象的なものを使用し、クライアントに詳細な情報が渡らないようにする
		// - InvalidArgumentException → 400 は toWpResponse 限定の挙動（toWpError / GraphQL では 500）。
		// categoryOf に含めると他トランスポートの契約が変わるため、ここでのみ扱う。
		$category = $e instanceof InvalidArgumentException ? ExceptionCategory::VALIDATION_FAILED : $this->categoryOf( $e );

		return new WP_REST_Response(
			array( 'message' => $category->clientMessage() ),
			$category->httpStatus()
		);
	}
}
