<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Content;

use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginInfoProvider;

class SlugProvider {
	public function __construct() {
		$this->text_domain = ( new WpPluginInfoProvider() )->textDomain();
	}
	private readonly string $text_domain;

	/**
	 * 管理画面メニューのルートで使用するスラッグを取得します。
	 */
	public function adminMenuRoot(): string {
		return $this->text_domain;
	}
}
