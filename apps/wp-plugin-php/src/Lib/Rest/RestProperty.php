<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Rest;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PluginInfoProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;

class RestProperty {

	public function namespace(): string {
		// 名前空間はプラグインのテキストドメインを使用
		// 外部サイトなど、第三者からのアクセスは想定していないためバージョニングは行わない
		return ( new PluginInfoProvider() )->textDomain();
	}

	public function graphQlRoute(): string {
		return 'graphql';
	}

	/**
	 * GraphQLのURLを取得します。
	 * ※末尾にスラッシュは含まれません。
	 *
	 * @return string
	 */
	public function graphQlUrl(): string {
		$wp_property = new WordPressPropertyProvider();
		return untrailingslashit( $wp_property->apiRootUrl() ) . '/' . $this->namespace() . '/' . $this->graphQlRoute();
	}
}
