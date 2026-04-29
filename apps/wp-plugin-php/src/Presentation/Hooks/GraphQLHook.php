<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\BadRequestException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\ForbiddenException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\UnauthorizedException;
use Cornix\Serendipity\Core\Presentation\GraphQL\RootValue;
use Cornix\Serendipity\Core\Infrastructure\GraphQL\PluginSchemaProvider;
use Cornix\Serendipity\Core\Infrastructure\GraphQL\Rule\MutationFieldLimitRule;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\RestPropertyProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpExceptionConverter;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpNonceService;
use GraphQL\GraphQL;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Psr\Container\ContainerInterface;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook extends HookBase {

	private ContainerInterface $container;

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {
		$rest_property = $this->container->get( RestPropertyProvider::class );
		// GraphQLのエンドポイントを登録
		$success = register_rest_route(
			$rest_property->namespace(),
			$rest_property->graphQlRoute(),
			array(
				'methods'             => 'POST',
				'callback'            => fn ( \WP_REST_Request $request ) => $this->callback( $request ),
				'permission_callback' => fn ( \WP_REST_Request $request ) => $this->permissionCallback( $request ),
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
		$logger              = $this->container->get( AppLogger::class );
		$nonce_service       = $this->container->get( WpNonceService::class );
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

		$schema     = $this->container->get( PluginSchemaProvider::class )->get();
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
				function ( array $errors, callable $formatter ): array {
					return array_map(
						function ( \GraphQL\Error\Error $error ) use ( $formatter ) {
							$prev_error = $error->getPrevious();

							$app_logger = $this->container->get( AppLogger::class );
							$app_logger->error( $error );
							if ( $prev_error ) {
								$app_logger->error( $prev_error );
							}

							$extensions = $error->getExtensions() ?? array();
							$message    = null;

							// エラーのメッセージを抽象的なものに置き換える
							if ( $prev_error instanceof BadRequestException ) { // 400
								$extensions['code'] = 'BAD_REQUEST';
								$message            = 'Bad Request';
							} elseif ( $prev_error instanceof UnauthorizedException ) { // 401
								$extensions['code'] = 'UNAUTHORIZED';
								$message            = 'Unauthorized';
							} elseif ( $prev_error instanceof PaymentRequiredException ) { // 402
								$extensions['code'] = 'PAYMENT_REQUIRED';
								$message            = 'Payment Required';
							} elseif ( $prev_error instanceof ForbiddenException ) { // 403
								$extensions['code'] = 'FORBIDDEN';
								$message            = 'Forbidden';
							} else {
								$extensions['code'] = 'INTERNAL_SERVER_ERROR';
								$message            = 'Internal Server Error';
							}

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
}
