<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

interface SiteService {
	/**
	 * インストール元URLと現在のサイトURLが不一致かどうかを返します。
	 * （trueの場合、サイトデータがコピーされた可能性があります）
	 */
	public function isInstallOriginUrlChanged(): bool;
}
