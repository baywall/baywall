<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\Exception\ValidationFailedException;
use Baywall\Core\Presentation\GraphQL\RootValue;
use Baywall\Core\Infrastructure\GraphQL\PluginSchemaProvider;
use Baywall\Core\Infrastructure\GraphQL\Rule\MutationFieldLimitRule;
use Baywall\Core\Infrastructure\WordPress\Service\ExceptionCategory;
use Baywall\Core\Infrastructure\WordPress\Content\RestPropertyProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpExceptionConverter;
use Baywall\Core\Presentation\Hooks\Base\HookBase;
use Baywall\Core\Infrastructure\WordPress\Auth\WpNonceService;
use GraphQL\GraphQL;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use GraphQL\Validator\Rules\ValidationRule;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook extends HookBase {
	/**
	 * @param \Closure(): RestPropertyProvider $rest_property_provider
	 * @param \Closure(): AppLogService        $app_log_service
	 * @param \Closure(): WpNonceService       $nonce_service
	 * @param \Closure(): WpExceptionConverter $exception_converter
	 * @param \Closure(): PluginSchemaProvider $plugin_schema_provider
	 * @param \Closure(): RootValue            $root_value
	 */
	public function __construct(
		private readonly \Closure $rest_property_provider,
		private readonly \Closure $app_log_service,
		private readonly \Closure $nonce_service,
		private readonly \Closure $exception_converter,
		private readonly \Closure $plugin_schema_provider,
		private readonly \Closure $root_value,
	) {}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {
		$rest_property_provider = ( $this->rest_property_provider )();
		$namespace              = $rest_property_provider->namespace();
		$route                  = $rest_property_provider->graphQlRoute();
		if ( ! $namespace || ! $route ) {
			throw new \RuntimeException( '[848379A8] GraphQL REST namespace and route must not be empty.' );
		}
		// GraphQLのエンドポイントを登録
		$success = register_rest_route(
			$namespace,
			$route,
			array(
				'methods'             => 'POST',
				'callback'            => $this->callback( ... ),
				'permission_callback' => $this->permissionCallback( ... ),
			)
		);

		assert( $success );
	}

	/**
	 * GraphQL RESTエンドポイントの実行可否を判定します。
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

	/**
	 * GraphQLクエリを実行します。
	 *
	 * @return array<string, mixed>|\WP_Error リクエストボディが不正な場合は WP_Error(400)
	 */
	public function callback( \WP_REST_Request $request ) {
		try {
			$input           = $this->extractGraphQlRequestParams( $request );
			$query           = $input['query'];
			$variable_values = $input['variables'];
			$operation_name  = $input['operationName'];
		} catch ( \Throwable $e ) {
			( $this->app_log_service )()->debug( $e );
			return ( $this->exception_converter )()->toWpError( $e );
		}

		$schema_instance     = ( $this->plugin_schema_provider )();
		$schema              = $schema_instance->get();
		$root_value_instance = ( $this->root_value )();
		$root_value          = $root_value_instance->get();

		$result = GraphQL::executeQuery( $schema, $query, $root_value, null, $variable_values, $operation_name, validationRules: $this->buildValidationRules() )
			// https://webonyx.github.io/graphql-php/error-handling/#custom-error-handling-and-formatting
			->setErrorsHandler(
				function ( array $errors, callable $formatter ): array {
					return array_map(
						function ( \GraphQL\Error\Error $error ) use ( $formatter ) {
							$prev_error = $error->getPrevious();

							$app_log_service = ( $this->app_log_service )();
							$app_log_service->error( $error );
							if ( $prev_error ) {
								$app_log_service->error( $prev_error );
							}

							$extensions = $error->getExtensions() ?? array();

							// エラーのメッセージを抽象的なものに置き換える
							// $prev_error が null の場合は INTERNAL_ERROR とする
							$exception_converter = ( $this->exception_converter )();
							$category            = ( null === $prev_error ) ? ExceptionCategory::INTERNAL_ERROR : $exception_converter->categoryOf( $prev_error );
							$extensions['code']  = $category->graphqlCode();
							$message             = $category->clientMessage();

							$new_error = new \GraphQL\Error\Error(
								$message, // $error->getPrevious()->getMessage(), // message
								null, // $error->getNodes(), // nodes
								null, // $error->getSource(), // source
								null, // $error->getPositions(), // positions
								null, // $error->getPath(), // path
								null, // $error, // previous
								$extensions // $error->getExtensions() // extensions
							);

							return $formatter( $new_error );
						},
						$errors
					);
				}
			)
			->toArray();

		return $result;
	}

	/**
	 * リクエストボディからGraphQLのクエリ・変数・オペレーション名を抽出します。
	 *
	 * @return array{query: string, variables: array<string, mixed>|null, operationName: string|null}
	 * @throws ValidationFailedException ボディがJSONオブジェクトでない、queryが無い・文字列でない、variablesがJSONオブジェクトでない、operationNameが文字列でない場合
	 */
	private function extractGraphQlRequestParams( \WP_REST_Request $request ): array {
		$input = json_decode( $request->get_body(), true );
		if ( ! is_array( $input ) || ! isset( $input['query'] ) || ! is_string( $input['query'] ) ) {
			throw new ValidationFailedException( '[138C76E4] query is required in request body.' );
		}

		$variable_values = $input['variables'] ?? null;
		// json_decode(連想配列)では JSON の {} と [] を区別できないため、空は空マップとして許容する
		$is_not_map = null !== $variable_values
			&& ( ! is_array( $variable_values ) || ( array() !== $variable_values && array_is_list( $variable_values ) ) );
		if ( $is_not_map ) {
			throw new ValidationFailedException( '[04F53341] variables must be an object in request body.' );
		}

		$operation_name = null;
		if ( array_key_exists( 'operationName', $input ) ) {
			if ( ! is_string( $input['operationName'] ) ) {
				throw new ValidationFailedException( '[2FD59D40] operationName must be a string in request body.' );
			}
			$operation_name = $input['operationName'];
		}

		return array(
			'query'         => $input['query'],
			'variables'     => $variable_values,
			'operationName' => $operation_name,
		);
	}

	/**
	 * リクエストごとのバリデーションルール配列を組み立てます。
	 * DocumentValidator::addRule() によるプロセス全体への静的状態の持ち越しを避けるため、
	 * executeQuery の第8引数に渡す配列として返します。
	 *
	 * @return array<class-string<ValidationRule>, ValidationRule>
	 */
	private function buildValidationRules(): array {
		// $validationRules を渡すとデフォルトルールは自動追加されないため、defaultRules() を土台に含める
		$rules                                  = DocumentValidator::defaultRules();
		$rules[ QueryComplexity::class ]        = new QueryComplexity( Config::GRAPHQL_MAX_COMPLEXITY );
		$rules[ QueryDepth::class ]             = new QueryDepth( Config::GRAPHQL_MAX_QUERY_DEPTH );
		$rules[ DisableIntrospection::class ]   = new DisableIntrospection( DisableIntrospection::ENABLED );
		$rules[ MutationFieldLimitRule::class ] = new MutationFieldLimitRule( Config::GRAPHQL_MUTATION_FIELD_MAX_COUNT );
		return $rules;
	}
}
