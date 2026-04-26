<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\GraphQLService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpConfig;

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
