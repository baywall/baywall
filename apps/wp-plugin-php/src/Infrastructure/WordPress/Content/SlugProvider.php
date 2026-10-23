<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Content;

use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginInfoProvider;

class SlugProvider {
	public function __construct(
		private readonly WpPluginInfoProvider $plugin_info_provider
	) {}

	/**
	 * 管理画面メニューのルートで使用するスラッグを取得します。
	 */
	public function adminMenuRoot(): string {
		return $this->plugin_info_provider->textDomain();
	}
}
