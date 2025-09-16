<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Presentation\GraphQL\RootValue;
use Cornix\Serendipity\Core\Lib\GraphQL\PluginSchemaProvider;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use DI\Container;
use GraphQL\GraphQL;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook extends HookBase {

	private Container $container;
	private ?RestProperty $rest_property;

	public function __construct( Container $container, ?RestProperty $rest_property = null ) {
		$this->container     = $container;
		$this->rest_property = $rest_property;
	}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {

		$rest_property = $this->rest_property ?? $this->container->get( RestProperty::class );

		// GraphQLのエンドポイントを登録
		$success = register_rest_route(
			$rest_property->namespace(),
			$rest_property->graphQlRoute(),
			array(
				'methods'             => 'POST',
				'callback'            => fn ( \WP_REST_Request $request ) => $this->callback( $request ),
				'permission_callback' => '__return_true',
			)
		);

		assert( $success );
	}

	public function callback( \WP_REST_Request $request ) {

		$app_logger = $this->container->get( AppLogger::class );

		// リクエストボディをデコード
		$input           = json_decode( $request->get_body(), true );
		$query           = $input['query'];
		$variable_values = isset( $input['variables'] ) ? $input['variables'] : null;

		$schema     = ( new PluginSchemaProvider() )->get();
		$root_value = ( new RootValue() )->get( $this->container );

		$result = GraphQL::executeQuery( $schema, $query, $root_value, null, $variable_values )
			// https://webonyx.github.io/graphql-php/error-handling/#custom-error-handling-and-formatting
			->setErrorsHandler(
				function ( array $errors, callable $formatter ) use ( $app_logger ): array {
					foreach ( $errors as $error ) {
						$app_logger->error( $error ); // エラーログを出力
					}
					return array_map( $formatter, $errors );
				}
			)
			->toArray();

		return $result;
	}
}
