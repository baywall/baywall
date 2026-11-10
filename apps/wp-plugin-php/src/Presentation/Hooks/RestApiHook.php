<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\UseCase\GetPaidContent;
use Baywall\Core\Application\UseCase\IssueAccessTokenByInvoiceToken;
use Baywall\Core\Application\UseCase\LogoutAuth;
use Baywall\Core\Application\UseCase\RefreshAccessToken;
use Baywall\Core\Constant\HttpStatus;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Domain\Exception\ValidationFailedException;
use Baywall\Core\Domain\Exception\AuthenticationFailedException;
use Baywall\Core\Domain\Exception\PaymentNotCompletedException;
use Baywall\Core\Domain\Service\CookieNameProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpExceptionConverter;
use Baywall\Core\Infrastructure\WordPress\Auth\WpNonceService;
use Throwable;
use WP_REST_Response;

/**
 * REST APIのフック登録(GraphQLを除く)
 */
class RestApiHook implements Hook {
	/**
	 * @param \Closure(): AppLogService                  $app_log_service
	 * @param \Closure(): WpNonceService                 $nonce_service
	 * @param \Closure(): WpExceptionConverter           $exception_converter
	 * @param \Closure(): CookieNameProvider             $cookie_name_provider
	 * @param \Closure(): RefreshAccessToken             $refresh_access_token
	 * @param \Closure(): IssueAccessTokenByInvoiceToken $issue_access_token_by_invoice_token
	 * @param \Closure(): LogoutAuth                     $logout_auth
	 * @param \Closure(): GetPaidContent                 $get_paid_content
	 */
	public function __construct(
		private readonly \Closure $app_log_service,
		private readonly \Closure $nonce_service,
		private readonly \Closure $exception_converter,
		private readonly \Closure $cookie_name_provider,
		private readonly \Closure $refresh_access_token,
		private readonly \Closure $issue_access_token_by_invoice_token,
		private readonly \Closure $logout_auth,
		private readonly \Closure $get_paid_content,
	) {}

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
				'callback'            => $this->authRefreshHandler( ... ),
				'permission_callback' => $this->permissionCallback( ... ),
			)
		);
		assert( $success );

		// 請求書トークンを用いてリフレッシュトークン＋アクセストークンを発行するAPIを登録
		$success = register_rest_route(
			WpConfig::REST_NAMESPACE,
			WpConfig::REST_ROUTE_AUTH_TOKEN_INVOICE,
			array(
				'methods'             => 'POST',
				'callback'            => $this->authTokenInvoiceHandler( ... ),
				'permission_callback' => $this->permissionCallback( ... ),
			)
		);
		assert( $success );

		// 有料コンテンツを取得するAPIを登録
		$success = register_rest_route(
			WpConfig::REST_NAMESPACE,
			WpConfig::REST_ROUTE_PAID_CONTENT,
			array(
				'methods'             => 'POST',
				'callback'            => $this->paidContentHandler( ... ),
				'permission_callback' => $this->permissionCallback( ... ),
			)
		);
		assert( $success );

		// ログアウトAPIを登録
		$success = register_rest_route(
			WpConfig::REST_NAMESPACE,
			WpConfig::REST_ROUTE_AUTH_LOGOUT,
			array(
				'methods'             => 'POST',
				'callback'            => $this->authLogoutHandler( ... ),
				'permission_callback' => $this->permissionCallback( ... ),
			)
		);
		assert( $success );
	}

	/**
	 * REST エンドポイントの実行可否を判定します。
	 *
	 * @return true|\WP_Error
	 */
	private function permissionCallback( \WP_REST_Request $request ): true|\WP_Error {
		$app_log_service     = ( $this->app_log_service )();
		$nonce_service       = ( $this->nonce_service )();
		$exception_converter = ( $this->exception_converter )();

		try {
			$nonce_service->checkRequestHeader( $request );
			return true;
		} catch ( \Throwable $e ) {
			$app_log_service->debug( $e );
			return $exception_converter->toWpError( $e );
		}
	}

	/** @return WP_REST_Response */
	public function authRefreshHandler( \WP_REST_Request $request ) {
		$cookie_name_provider = ( $this->cookie_name_provider )();

		// リフレッシュトークンを取得
		$refresh_token_value = $_COOKIE[ $cookie_name_provider->refreshToken() ] ?? null;

		try {
			// リフレッシュトークンが送信されていない場合は例外をスロー
			if ( $refresh_token_value === null ) {
				throw new AuthenticationFailedException( '[A018971D] Refresh token is missing.' );
			}

			( $this->refresh_access_token )()->handle( $refresh_token_value );
			return new WP_REST_Response( array(), HttpStatus::OK );
		} catch ( Throwable $e ) {
			if ( $e instanceof AuthenticationFailedException ) {
				// アクセストークンが存在しない状態でも
				// 初回アクセス時にアクセスするためログレベルを抑制
				( $this->app_log_service )()->debug( $e );
			} else {
				( $this->app_log_service )()->error( $e );
			}

			return ( $this->exception_converter )()->toWpResponse( $e );
		}
	}

	/** @return WP_REST_Response */
	public function authTokenInvoiceHandler( \WP_REST_Request $request ) {
		$cookie_name_provider = ( $this->cookie_name_provider )();

		// 請求書トークンをCookieから取得
		/** @var string|null */
		$invoice_token_string_value = $_COOKIE[ $cookie_name_provider->invoiceToken() ] ?? null;

		try {
			if ( $invoice_token_string_value === null ) {
				throw new AuthenticationFailedException( '[A693201D] Invoice token is missing.' );
			}

			( $this->issue_access_token_by_invoice_token )()->handle( $invoice_token_string_value );
			return new WP_REST_Response( array(), HttpStatus::OK );
		} catch ( Throwable $e ) {
			if ( $e instanceof PaymentNotCompletedException ) {
				// 支払い未確認は想定内の状態なのでログレベルを抑制
				( $this->app_log_service )()->info( $e );
			} else {
				( $this->app_log_service )()->error( $e );
			}

			return ( $this->exception_converter )()->toWpResponse( $e );
		}
	}

	/** @return WP_REST_Response */
	public function authLogoutHandler( \WP_REST_Request $request ) {
		$cookie_name_provider = ( $this->cookie_name_provider )();

		// リフレッシュトークンをCookieから取得
		/** @var string|null */
		$refresh_token_value = $_COOKIE[ $cookie_name_provider->refreshToken() ] ?? null;

		try {
			( $this->logout_auth )()->handle( $refresh_token_value );
			return new WP_REST_Response( array(), HttpStatus::OK );
		} catch ( Throwable $e ) {
			( $this->app_log_service )()->error( $e );
			return ( $this->exception_converter )()->toWpResponse( $e );
		}
	}

	/** @return WP_REST_Response */
	public function paidContentHandler( \WP_REST_Request $request ) {
		try {
			$post_id = $this->extractPostId( $request );

			$paid_content = ( $this->get_paid_content )()->handle( $post_id );
			return new WP_REST_Response(
				array( 'paidContent' => $paid_content ),
				HttpStatus::OK
			);
		} catch ( Throwable $e ) {
			if ( $e instanceof PaymentNotCompletedException ) {
				// 未購入・未確認は想定内の状態なのでログレベルを抑制
				( $this->app_log_service )()->info( $e );
			} else {
				( $this->app_log_service )()->error( $e );
			}

			return ( $this->exception_converter )()->toWpResponse( $e );
		}
	}

	/** リクエストボディからpostIdを抽出します */
	private function extractPostId( \WP_REST_Request $request ): int {
		$body = json_decode( $request->get_body(), true );
		if ( ! is_array( $body ) || ! array_key_exists( 'postId', $body ) ) {
			throw new ValidationFailedException( '[EA4E29F2] postId is required in request body.' );
		}

		$post_id = $body['postId'];
		if ( ! is_int( $post_id ) || $post_id <= 0 ) {
			throw new ValidationFailedException( '[D974364F] Invalid postId.' );
		}

		return $post_id;
	}
}
