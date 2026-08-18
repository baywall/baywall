<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\GraphQLService;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;

class WpGraphQLService implements GraphQLService {
	/** @inheritDoc */
	public function getSchemaFilePath(): string {
		return WpConfig::GRAPHQL_SCHEMA_PATH;
	}

	/** @inheritDoc */
	public function getCacheFilePath(): string {
		return WpConfig::GRAPHQL_CACHE_PATH;
	}
}
