<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Constant\Config;
use Baywall\Core\Presentation\GraphQL\RootValue;
use Baywall\Core\Infrastructure\GraphQL\PluginSchemaProvider;
use Baywall\Core\Infrastructure\GraphQL\Rule\MutationFieldLimitRule;
use Baywall\Core\Infrastructure\WordPress\Service\ExceptionCategory;
use Baywall\Core\Infrastructure\WordPress\Service\RestPropertyProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpExceptionConverter;
use Baywall\Core\Presentation\Hooks\Base\HookBase;
use Baywall\Core\Infrastructure\WordPress\Service\WpNonceService;
use GraphQL\GraphQL;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use GraphQL\Validator\Rules\ValidationRule;
use DI\Container;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook extends HookBase {


	public function __construct( private readonly Container $container ) {}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {
		/** @var RestPropertyProvider */
		$rest_property = $this->container->get( RestPropertyProvider::class );
		// GraphQLのエンドポイントを登録
		$success = register_rest_route(
			$rest_property->namespace(),
			$rest_property->graphQlRoute(),
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
	 * @return true|WP_Error
	 */
	private function permissionCallback( \WP_REST_Request $request ) {
		/** @var AppLogger */
		$logger = $this->container->get( AppLogger::class );
		/** @var WpNonceService */
		$nonce_service = $this->container->get( WpNonceService::class );
		/** @var WpExceptionConverter */
		$exception_converter = $this->container->get( WpExceptionConverter::class );

		try {
			$nonce_service->checkRequestHeader( $request );
			return true;
		} catch ( \Throwable $e ) {
			$logger->debug( $e );
			return $exception_converter->toWpError( $e );
		}
	}

	public function callback( \WP_REST_Request $request ) {
		// リクエストボディをデコード
		$input           = json_decode( $request->get_body(), true );
		$query           = $input['query'];
		$variable_values = isset( $input['variables'] ) ? $input['variables'] : null;

		/** @var PluginSchemaProvider */
		$schema_instance = $this->container->get( PluginSchemaProvider::class );
		$schema          = $schema_instance->get();
		/** @var RootValue */
		$root_value_instance = $this->container->get( RootValue::class );
		$root_value          = $root_value_instance->get();

		$result = GraphQL::executeQuery( $schema, $query, $root_value, null, $variable_values, validationRules: $this->buildValidationRules() )
			// https://webonyx.github.io/graphql-php/error-handling/#custom-error-handling-and-formatting
			->setErrorsHandler(
				function ( array $errors, callable $formatter ): array {
					return array_map(
						function ( \GraphQL\Error\Error $error ) use ( $formatter ) {
							$prev_error = $error->getPrevious();

							/** @var AppLogger */
							$app_logger = $this->container->get( AppLogger::class );
							$app_logger->error( $error );
							if ( $prev_error ) {
								$app_logger->error( $prev_error );
							}

							$extensions = $error->getExtensions() ?? array();

							// エラーのメッセージを抽象的なものに置き換える
							// $prev_error が null の場合は INTERNAL_ERROR とする
							/** @var WpExceptionConverter */
							$exception_converter = $this->container->get( WpExceptionConverter::class );
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
	 * リクエストごとのバリデーションルール配列を組み立てます。
	 * DocumentValidator::addRule() によるプロセス全体への静的状態の持ち越しを避けるため、
	 * executeQuery の第8引数に渡す配列として返します。
	 *
	 * @return array<class-string<ValidationRule>, ValidationRule>
	 */
	private function buildValidationRules(): array {
		// $validationRules を渡すとデフォルトルールは自動追加されないため、defaultRules() を土台に含める
		$rules                           = DocumentValidator::defaultRules();
		$rules[ QueryComplexity::class ] = new QueryComplexity( Config::GRAPHQL_MAX_COMPLEXITY );
		$rules[ QueryDepth::class ]      = new QueryDepth( Config::GRAPHQL_MAX_QUERY_DEPTH );
		if ( Config::GRAPHQL_DISABLE_INTROSPECTION ) {
			$rules[ DisableIntrospection::class ] = new DisableIntrospection( DisableIntrospection::ENABLED );
		}
		$rules[ MutationFieldLimitRule::class ] = new MutationFieldLimitRule( Config::GRAPHQL_MUTATION_FIELD_MAX_COUNT );
		return $rules;
	}
}
