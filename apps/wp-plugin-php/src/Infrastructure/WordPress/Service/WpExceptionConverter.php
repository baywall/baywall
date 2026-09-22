<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Constant\HttpStatus;
use Baywall\Core\Domain\Exception\HttpStatus\BadRequestException;
use Baywall\Core\Domain\Exception\HttpStatus\ForbiddenException;
use Baywall\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Baywall\Core\Domain\Exception\HttpStatus\UnauthorizedException;
use InvalidArgumentException;
use WP_Error;
use WP_REST_Response;

/** WordPressの例外変換サービス */
class WpExceptionConverter {

	public function toWpError( \Throwable $e ): WP_Error {
		// WP_Error 型の場合はそのまま返す
		if ( $e instanceof WP_Error ) {
			assert( false, '[7D72E2C8] ' . var_export( $e, true ) ); // 通常ここは通らない
			return $e;
		}

		// 独自例外クラスを WP_Error 型に変換して返す
		return match ( true ) {
			$e instanceof BadRequestException => new WP_Error( // 400 Bad Request
				'bad_request',
				'Bad Request',
				array( 'status' => HttpStatus::BAD_REQUEST )
			),
			$e instanceof UnauthorizedException => new WP_Error( // 401 Unauthorized
				'unauthorized',
				'Unauthorized',
				array( 'status' => HttpStatus::UNAUTHORIZED )
			),
			$e instanceof PaymentRequiredException => new WP_Error( // 402 Payment Required
				'payment_required',
				'Payment Required',
				array( 'status' => HttpStatus::PAYMENT_REQUIRED )
			),
			$e instanceof ForbiddenException => new WP_Error( // 403 Forbidden
				'forbidden',
				'Forbidden',
				array( 'status' => HttpStatus::FORBIDDEN )
			),
			default => new WP_Error( // その他の例外は 500 Internal Server Error として返す
				'internal_server_error',
				'Internal Server Error',
				array( 'status' => HttpStatus::INTERNAL_SERVER_ERROR )
			),
		};
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
		return match ( true ) {
			$e instanceof BadRequestException || $e instanceof InvalidArgumentException => new WP_REST_Response( // 400 Bad Request
				array( 'message' => 'Bad Request' ),
				HttpStatus::BAD_REQUEST
			),
			$e instanceof UnauthorizedException => new WP_REST_Response( // 401 Unauthorized
				array( 'message' => 'Unauthorized' ),
				HttpStatus::UNAUTHORIZED
			),
			$e instanceof PaymentRequiredException => new WP_REST_Response( // 402 Payment Required
				array( 'message' => 'Payment Required' ),
				HttpStatus::PAYMENT_REQUIRED
			),
			$e instanceof ForbiddenException => new WP_REST_Response( // 403 Forbidden
				array( 'message' => 'Forbidden' ),
				HttpStatus::FORBIDDEN
			),
			default => new WP_REST_Response( // その他の例外は 500 Internal Server Error として返す
				array( 'message' => 'Internal Server Error' ),
				HttpStatus::INTERNAL_SERVER_ERROR
			),
		};
	}
}
