<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\UseCase\RefreshAccessToken;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\Exception\UnauthorizedAccessException;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use DI\Container;

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
		// アクセストークン発行用のエンドポイントを登録
		$success = register_rest_route(
			WpConfig::REST_NAMESPACE,
			WpConfig::REST_ROUTE_AUTH_REFRESH,
			array(
				// OAuth 2.0 ではアクセストークンリクエスト時にPOSTメソッドを使う。ここでもそれに従う。
				//
				// @see [RFC 6749 - The OAuth 2.0 Authorization Framework 日本語訳](https://tex2e.github.io/rfc-translater/html/rfc6749.html)
				// > The client MUST use the HTTP "POST" method when making access token requests.
				// > クライアントは、アクセストークンリクエストを行うときにHTTPの「POST」メソッドを使用する必要があります。
				'methods'             => 'POST',
				'callback'            => fn ( \WP_REST_Request $request ) => $this->authRefreshHandler( $request ),
				'permission_callback' => '__return_true',
			)
		);

		assert( $success );
	}

	public function authRefreshHandler( \WP_REST_Request $request ) {
		// リフレッシュトークンを取得
		$refresh_token_value = $_COOKIE[ WpConfig::COOKIE_NAME_REFRESH_TOKEN ] ?? null;
		$app_logger          = $this->container->get( AppLogger::class );

		try {
			// リフレッシュトークンが送信されていない場合は例外をスロー
			if ( $refresh_token_value === null ) {
				throw new UnauthorizedAccessException( '[A018971D] Refresh token is missing.' );
			}

			$access_token_value = $this->container->get( RefreshAccessToken::class )->handle( $refresh_token_value );

			return array(
				'access_token' => $access_token_value,
			);
		} catch ( UnauthorizedAccessException $e ) {
			$app_logger->debug( $e ); // 大量にアクセスされる可能性があるため、debugレベルでログ出力
			// リフレッシュトークンが無効な場合、401エラーを返す
			return new \WP_REST_Response(
				array( 'message' => 'Unauthorized' ),
				self::HTTP_STATUS_401_UNAUTHORIZED
			);
		}
	}
}
