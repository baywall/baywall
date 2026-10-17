<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

interface SiteService {
	/**
	 * 初回インストール時のURLと現在のサイトURLが不一致かどうかを返します。
	 * （trueの場合、サイトデータがコピーされた可能性があります）
	 */
	public function hasSiteUrlChangedSinceInstall(): bool;
}
