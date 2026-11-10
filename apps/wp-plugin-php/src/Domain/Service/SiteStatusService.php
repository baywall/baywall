<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\Repository\PausedRepository;
use Baywall\Core\Domain\Repository\SellerRepository;

class SiteStatusService {
	public function __construct(
		private readonly PausedRepository $paused_repository,
		private readonly SiteUrlChecker $site_url_checker,
		private readonly SellerRepository $seller_repository,
		private readonly MinimumSupportedVersionChecker $minimum_supported_version_checker
	) {}

	/**
	 * 取引を一時停止すべき状態かどうかを返します
	 * （以下のいずれかの場合は true を返す）
	 * - 販売者ウォレットが取得できない場合
	 * - サイト一時停止中
	 * - 初回インストール時のURLが変更された場合
	 * - プラグインバージョンが最低サポートバージョン未満の場合（取得不可の場合は停止しない）
	 */
	public function isTransactionSuspended(): bool {
		return $this->seller_repository->get() === null
			|| $this->paused_repository->get()
			|| $this->site_url_checker->hasSiteUrlChangedSinceInstall()
			|| ( $this->minimum_supported_version_checker->isBelowMinimumSupportedVersion() ?? false );
	}
}
