<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Constant\HttpStatus;

/**
 * 例外のビジネス分類とトランスポート契約値の翻訳表
 *
 * HTTPステータス・WP_Errorコード・GraphQL extensions.code・クライアント向けメッセージの対応は公開契約であり変更しない。
 */
enum ExceptionCategory {
	case VALIDATION_FAILED;
	case AUTHENTICATION_FAILED;
	case PAYMENT_NOT_COMPLETED;
	case PERMISSION_DENIED;
	case INTERNAL_ERROR;

	public function httpStatus(): int {
		return match ( $this ) {
			self::VALIDATION_FAILED => HttpStatus::BAD_REQUEST,
			self::AUTHENTICATION_FAILED => HttpStatus::UNAUTHORIZED,
			self::PAYMENT_NOT_COMPLETED => HttpStatus::PAYMENT_REQUIRED,
			self::PERMISSION_DENIED => HttpStatus::FORBIDDEN,
			self::INTERNAL_ERROR => HttpStatus::INTERNAL_SERVER_ERROR,
		};
	}

	public function wpErrorCode(): string {
		return match ( $this ) {
			self::VALIDATION_FAILED => 'bad_request',
			self::AUTHENTICATION_FAILED => 'unauthorized',
			self::PAYMENT_NOT_COMPLETED => 'payment_required',
			self::PERMISSION_DENIED => 'forbidden',
			self::INTERNAL_ERROR => 'internal_server_error',
		};
	}

	public function graphqlCode(): string {
		return match ( $this ) {
			self::VALIDATION_FAILED => 'BAD_REQUEST',
			self::AUTHENTICATION_FAILED => 'UNAUTHORIZED',
			self::PAYMENT_NOT_COMPLETED => 'PAYMENT_REQUIRED',
			self::PERMISSION_DENIED => 'FORBIDDEN',
			self::INTERNAL_ERROR => 'INTERNAL_SERVER_ERROR',
		};
	}

	public function clientMessage(): string {
		return match ( $this ) {
			self::VALIDATION_FAILED => 'Bad Request',
			self::AUTHENTICATION_FAILED => 'Unauthorized',
			self::PAYMENT_NOT_COMPLETED => 'Payment Required',
			self::PERMISSION_DENIED => 'Forbidden',
			self::INTERNAL_ERROR => 'Internal Server Error',
		};
	}
}
