<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Domain\Service\PluginInfoProvider;

class ResolvePluginVersion {


	public function __construct( private readonly PluginInfoProvider $plugin_info_provider ) {}

	public function handle( array $root_value, array $args ): string {
		// アクセス制御は不要
		return $this->plugin_info_provider->version();
	}
}
