<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Domain\Service\PluginInfoProvider;

class ResolvePluginVersion {

	private PluginInfoProvider $plugin_info_provider;

	public function __construct(
		PluginInfoProvider $plugin_info_provider
	) {
		$this->plugin_info_provider = $plugin_info_provider;
	}

	public function handle( array $root_value, array $args ): string {
		// アクセス制御は不要
		return $this->plugin_info_provider->version();
	}
}
