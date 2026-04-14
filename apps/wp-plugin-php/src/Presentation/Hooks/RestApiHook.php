<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\UseCase\GetPaidContent;
use Cornix\Serendipity\Core\Application\UseCase\IssueAccessTokenByInvoiceToken;
use Cornix\Serendipity\Core\Application\UseCase\RefreshAccessToken;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\BadRequestException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\ForbiddenException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\UnauthorizedException;
use Cornix\Serendipity\Core\Domain\Service\CookieNameProvider;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use DI\Container;
use InvalidArgumentException;
use Throwable;
use WP_REST_Response;

/**
 * REST APIのフック登録(GraphQLを除く)
 */
class RestApiHook extends HookBase {

	private Container $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {
		// OAuth 2.0 ではアクセストークンリクエスト時にPOSTメソッドを使う。ここで登録するアクセストークン発行APIもそれに倣い、POSTメソッドを使用する。
		//
		// @see [RFC 6749 - The OAuth 2.0 Authorization Framework 日本語訳](https://tex2e.github.io/rfc-translater/html/rfc6749.html)
		// > The client MUST use the HTTP "POST" method when making access token requests.
		// > クライアントは、アクセストークンリクエストを行うときにHTTPの「POST」メソッドを使用する必要があります。

		// リフレッシュトークンを用いてアクセストークンを発行するAPIを登録
		$success = register_rest_route(
			WpConfig::REST_NAMESPACE,
			WpConfig::REST_ROUTE_AUTH_REFRESH,
			array(
				'methods'             => 'POST',
				'callback'            => fn ( \WP_REST_Request $request ) => $this->authRefreshHandler( $request ),
				'permission_callback' => '__return_true',
			)
		);
		assert( $success );

		// 請求書トークンを用いてアクセストークンを発行するAPIを登録
		$success = register_rest_route(
			WpConfig::REST_NAMESPACE,
			WpConfig::REST_ROUTE_AUTH_TOKEN_INVOICE,
			array(
				'methods'             => 'POST',
				'callback'            => fn ( \WP_REST_Request $request ) => $this->authTokenInvoiceHandler( $request ),
				'permission_callback' => '__return_true',
			)
		);
		assert( $success );

		// 有料コンテンツを取得するAPIを登録
		$success = register_rest_route(
			WpConfig::REST_NAMESPACE,
			WpConfig::REST_ROUTE_PAID_CONTENT,
			array(
				'methods'             => 'POST',
				'callback'            => fn ( \WP_REST_Request $request ) => $this->paidContentHandler( $request ),
				'permission_callback' => '__return_true',
			)
		);
		assert( $success );
	}

	public function authRefreshHandler( \WP_REST_Request $request ) {
		$cookie_name_provider = $this->container->get( CookieNameProvider::class );

		// リフレッシュトークンを取得
		$refresh_token_value = $_COOKIE[ $cookie_name_provider->refreshToken() ] ?? null;

		try {
			// リフレッシュトークンが送信されていない場合は例外をスロー
			if ( $refresh_token_value === null ) {
				throw new UnauthorizedException( '[A018971D] Refresh token is missing.' );
			}

			$this->container->get( RefreshAccessToken::class )->handle( $refresh_token_value );
			return new WP_REST_Response( array(), self::HTTP_STATUS_200_OK );
		} catch ( UnauthorizedException $e ) {
			$this->container->get( AppLogger::class )->debug( $e );
			// リフレッシュトークンが無効な場合、401エラーを返す
			return new WP_REST_Response(
				array( 'message' => 'Unauthorized' ),
				self::HTTP_STATUS_401_UNAUTHORIZED
			);
		} catch ( Throwable $e ) {
			$this->container->get( AppLogger::class )->error( $e );
			return new WP_REST_Response(
				array( 'message' => 'Internal Server Error' ),
				self::HTTP_STATUS_500_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function authTokenInvoiceHandler( \WP_REST_Request $request ) {
		$cookie_name_provider = $this->container->get( CookieNameProvider::class );

		// 請求書トークンをCookieから取得
		/** @var string|null */
		$invoice_token_string_value = $_COOKIE[ $cookie_name_provider->invoiceToken() ] ?? null;

		try {
			if ( $invoice_token_string_value === null ) {
				throw new UnauthorizedException( '[A693201D] Invoice token is missing.' );
			}

			$this->container->get( IssueAccessTokenByInvoiceToken::class )->handle( $invoice_token_string_value );
			return new WP_REST_Response( array(), self::HTTP_STATUS_200_OK );
		} catch ( UnauthorizedException $e ) {
			$this->container->get( AppLogger::class )->debug( $e );
			return new WP_REST_Response(
				array( 'message' => 'Unauthorized' ),
				self::HTTP_STATUS_401_UNAUTHORIZED
			);
		} catch ( PaymentRequiredException $e ) {
			$this->container->get( AppLogger::class )->debug( $e );
			return new WP_REST_Response(
				array( 'message' => 'Payment Required' ),
				self::HTTP_STATUS_402_PAYMENT_REQUIRED
			);
		} catch ( Throwable $e ) {
			$this->container->get( AppLogger::class )->error( $e );
			return new WP_REST_Response(
				array( 'message' => 'Internal Server Error' ),
				self::HTTP_STATUS_500_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function paidContentHandler( \WP_REST_Request $request ) {
		try {
			$post_id = $this->extractPostId( $request );

			$paid_content = $this->container->get( GetPaidContent::class )->handle( $post_id );
			return new WP_REST_Response(
				array( 'paidContent' => $paid_content ),
				self::HTTP_STATUS_200_OK
			);
		} catch ( BadRequestException | InvalidArgumentException $e ) {
			$this->container->get( AppLogger::class )->debug( $e );
			return new WP_REST_Response(
				array( 'message' => 'Bad Request' ),
				self::HTTP_STATUS_400_BAD_REQUEST
			);
		} catch ( UnauthorizedException $e ) {
			$this->container->get( AppLogger::class )->debug( $e );
			return new WP_REST_Response(
				array( 'message' => 'Unauthorized' ),
				self::HTTP_STATUS_401_UNAUTHORIZED
			);
		} catch ( PaymentRequiredException $e ) {
			$this->container->get( AppLogger::class )->debug( $e );
			return new WP_REST_Response(
				array( 'message' => 'Payment Required' ),
				self::HTTP_STATUS_402_PAYMENT_REQUIRED
			);
		} catch ( ForbiddenException $e ) {
			$this->container->get( AppLogger::class )->debug( $e );
			return new WP_REST_Response(
				array( 'message' => 'Forbidden' ),
				self::HTTP_STATUS_403_FORBIDDEN
			);
		} catch ( Throwable $e ) {
			$this->container->get( AppLogger::class )->error( $e );
			return new WP_REST_Response(
				array( 'message' => 'Internal Server Error' ),
				self::HTTP_STATUS_500_INTERNAL_SERVER_ERROR
			);
		}
	}

	/** リクエストボディからpostIdを抽出します */
	private function extractPostId( \WP_REST_Request $request ): int {
		$body = json_decode( $request->get_body(), true );
		if ( ! is_array( $body ) || ! array_key_exists( 'postId', $body ) ) {
			throw new BadRequestException( '[EA4E29F2] postId is required in request body.' );
		}

		$post_id = $body['postId'];
		if ( ! is_int( $post_id ) || $post_id <= 0 ) {
			throw new BadRequestException( '[D974364F] Invalid postId.' );
		}

		return $post_id;
	}
}
