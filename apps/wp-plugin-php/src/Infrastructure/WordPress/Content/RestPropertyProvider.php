<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Content;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginInfoProvider;

class RestPropertyProvider {

	public function namespace(): string {
		// 名前空間はプラグインのテキストドメインを使用
		// 外部サイトなど、第三者からのアクセスは想定していないためバージョニングは行わない
		return ( new WpPluginInfoProvider() )->textDomain();
	}

	public function graphQlRoute(): string {
		return WpConfig::GRAPHQL_ROUTE;
	}
}
