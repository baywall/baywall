<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\GraphQL;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Presentation\GraphQL\Resolvers\Resolver;

class RootValue {


	/**
	 * @param array<string, \Closure(): Resolver> $resolvers フィールド名 → resolver 生成 closure の対応表
	 */
	public function __construct(
		private readonly array $resolvers,
		private readonly AppLogger $logger
	) {}

	/**
	 * @return array<string, mixed>
	 */
	public function get(): array {

		$result = array();
		foreach ( $this->resolvers as $field => $resolver_factory ) {
			$result[ $field ] = function ( array $root_value, array $args ) use ( $resolver_factory ) {
				try {
					$resolver = $resolver_factory();
					return $resolver->handle( $root_value, $args );
				} catch ( \Throwable $e ) {
					$this->logger->error( $e );
					throw $e;
				}
			};
		}

		return $result;
	}
}
