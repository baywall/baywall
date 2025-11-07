<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Presentation\GraphQL\RootValue;
use Cornix\Serendipity\Core\Infrastructure\GraphQL\PluginSchemaProvider;
use Cornix\Serendipity\Core\Infrastructure\GraphQL\Rule\MutationFieldLimitRule;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\RestPropertyProvider;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use DI\Container;
use GraphQL\GraphQL;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook extends HookBase {

	private Container $container;
	private RestPropertyProvider $rest_property;

	public function __construct( Container $container, ?RestPropertyProvider $rest_property = null ) {
		$this->container     = $container;
		$this->rest_property = $rest_property ?? $container->get( RestPropertyProvider::class );
	}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {
		// GraphQLのエンドポイントを登録
		$success = register_rest_route(
			$this->rest_property->namespace(),
			$this->rest_property->graphQlRoute(),
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
		$root_value = $this->container->get( RootValue::class )->get();

		// クエリの複雑度制限を追加
		DocumentValidator::addRule( new QueryComplexity( Config::GRAPHQL_MAX_COMPLEXITY ) );
		// クエリの深度制限を追加
		DocumentValidator::addRule( new QueryDepth( Config::GRAPHQL_MAX_QUERY_DEPTH ) );
		// イントロスペクションの無効化
		if ( Config::GRAPHQL_DISABLE_INTROSPECTION ) {
			DocumentValidator::addRule( new DisableIntrospection( DisableIntrospection::ENABLED ) );
		}
		// 独自ルール追加
		// mutation呼び出し時のフィールド数制限ルールを追加
		DocumentValidator::addRule( new MutationFieldLimitRule( Config::GRAPHQL_MUTATION_FIELD_MAX_COUNT ) );

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
