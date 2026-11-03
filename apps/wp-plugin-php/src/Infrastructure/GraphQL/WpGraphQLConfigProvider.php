<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\GraphQL;

use Baywall\Core\Application\Port\GraphQLConfigProvider;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;

class WpGraphQLConfigProvider implements GraphQLConfigProvider {
	/** @inheritDoc */
	public function getSchemaFilePath(): string {
		return WpConfig::GRAPHQL_SCHEMA_PATH;
	}

	/** @inheritDoc */
	public function getCacheFilePath(): string {
		return WpConfig::GRAPHQL_CACHE_PATH;
	}
}
