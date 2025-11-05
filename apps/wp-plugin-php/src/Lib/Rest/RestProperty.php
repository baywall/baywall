<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Rest;

use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PluginInfoProvider;

class RestProperty {

	public function namespace(): string {
		// 名前空間はプラグインのテキストドメインを使用
		// 外部サイトなど、第三者からのアクセスは想定していないためバージョニングは行わない
		return ( new PluginInfoProvider() )->textDomain();
	}

	public function graphQlRoute(): string {
		return WpConfig::GRAPHQL_ROUTE;
	}
}
