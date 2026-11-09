<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AuditLogService;
use Baywall\Core\Application\Repository\PurgeOnUninstallRepository;
use Baywall\Core\Application\Repository\SctaUrlRepository;
use Baywall\Core\Application\Repository\ThemeSettingRepository;
use Baywall\Core\Application\Port\HomeUrlProvider;
use Baywall\Core\Application\Service\TransactionRunner;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\PausedRepository;
use Baywall\Core\Domain\ValueObject\SctaUrl;
use Baywall\Core\Domain\ValueObject\ThemeSetting;

class SaveSiteSettings {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly PausedRepository $paused_repository,
		private readonly SctaUrlRepository $scta_url_repository,
		private readonly PurgeOnUninstallRepository $purge_on_uninstall_repository,
		private readonly ThemeSettingRepository $theme_setting_repository,
		private readonly TransactionRunner $transaction_runner,
		private readonly HomeUrlProvider $home_url_provider,
		private readonly AuditLogService $audit_log_service
	) {}

	/** サイト設定を保存します */
	public function handle( bool $paused, ?SctaUrl $scta_url, bool $purge_on_uninstall, ThemeSetting $theme_setting ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		if ( $scta_url !== null && ! str_starts_with( $scta_url->value(), $this->home_url_provider->homeUrl() ) ) {
			throw new \InvalidArgumentException( '[57280AEF] sctaUrl must start with homeUrl.' );
		}

		$result = $this->transaction_runner->transactional(
			function () use ( $paused, $scta_url, $purge_on_uninstall, $theme_setting ) {
				$this->paused_repository->save( $paused );
				$this->scta_url_repository->save( $scta_url );
				$this->purge_on_uninstall_repository->save( $purge_on_uninstall );
				$this->theme_setting_repository->save( $theme_setting );

				return true;
			}
		);

		$this->audit_log_service->info(
			'save_site_settings',
			'Site settings saved. paused: ' . ( $paused ? 'true' : 'false' ) . ', purgeOnUninstall: ' . ( $purge_on_uninstall ? 'true' : 'false' ) . ", themeSetting: {$theme_setting}, sctaUrl: " . ( $scta_url === null ? 'null' : $scta_url->value() )
		);

		return $result;
	}
}
