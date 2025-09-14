<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Repository\PluginInfoProvider;

class SlugProvider {
	public function __construct() {
		$this->text_domain = ( new PluginInfoProvider() )->textDomain();
	}
	private string $text_domain;

	/**
	 * 管理画面メニューのルートで使用するスラッグを取得します。
	 */
	public function adminMenuRoot(): string {
		return $this->text_domain;
	}
}
