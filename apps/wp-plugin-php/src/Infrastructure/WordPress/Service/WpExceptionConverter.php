<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\HttpStatus;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\BadRequestException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\ForbiddenException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\UnauthorizedException;
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
		if ( $e instanceof BadRequestException ) { // 400 Bad Request
			return new WP_Error(
				'bad_request',
				'Bad Request',
				array( 'status' => HttpStatus::BAD_REQUEST )
			);
		} elseif ( $e instanceof UnauthorizedException ) { // 401 Unauthorized
			return new WP_Error(
				'unauthorized',
				'Unauthorized',
				array( 'status' => HttpStatus::UNAUTHORIZED )
			);
		} elseif ( $e instanceof PaymentRequiredException ) { // 402 Payment Required
			return new WP_Error(
				'payment_required',
				'Payment Required',
				array( 'status' => HttpStatus::PAYMENT_REQUIRED )
			);
		} elseif ( $e instanceof ForbiddenException ) { // 403 Forbidden
			return new WP_Error(
				'forbidden',
				'Forbidden',
				array( 'status' => HttpStatus::FORBIDDEN )
			);
		} else { // その他の例外は 500 Internal Server Error として返す
			return new WP_Error(
				'internal_server_error',
				'Internal Server Error',
				array( 'status' => HttpStatus::INTERNAL_SERVER_ERROR )
			);
		}
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
		if ( $e instanceof BadRequestException || $e instanceof InvalidArgumentException ) { // 400 Bad Request
			return new WP_REST_Response(
				array( 'message' => 'Bad Request' ),
				HttpStatus::BAD_REQUEST
			);
		} elseif ( $e instanceof UnauthorizedException ) { // 401 Unauthorized
			return new WP_REST_Response(
				array( 'message' => 'Unauthorized' ),
				HttpStatus::UNAUTHORIZED
			);
		} elseif ( $e instanceof PaymentRequiredException ) { // 402 Payment Required
			return new WP_REST_Response(
				array( 'message' => 'Payment Required' ),
				HttpStatus::PAYMENT_REQUIRED
			);
		} elseif ( $e instanceof ForbiddenException ) { // 403 Forbidden
			return new WP_REST_Response(
				array( 'message' => 'Forbidden' ),
				HttpStatus::FORBIDDEN
			);
		} else { // その他の例外は 500 Internal Server Error として返す
			return new WP_REST_Response(
				array( 'message' => 'Internal Server Error' ),
				HttpStatus::INTERNAL_SERVER_ERROR
			);
		}
	}
}
