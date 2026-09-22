<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Repository\PurgeOnUninstallRepository;
use Baywall\Core\Application\Repository\SctaUrlRepository;
use Baywall\Core\Application\Repository\ThemeSettingRepository;
use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\PausedRepository;
use Baywall\Core\Domain\ValueObject\SctaUrl;
use Baywall\Core\Domain\ValueObject\ThemeSetting;
use Baywall\Core\Infrastructure\Util\Strings;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;

class ResolveSaveSiteSettings {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly PausedRepository $paused_repository,
		private readonly SctaUrlRepository $scta_url_repository,
		private readonly PurgeOnUninstallRepository $purge_on_uninstall_repository,
		private readonly ThemeSettingRepository $theme_setting_repository,
		private readonly TransactionService $transaction_service,
		private readonly WordPressPropertyProvider $wordpress_property_provider
	) {}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var array{paused: bool, sctaUrl?: string|null, purgeOnUninstall: bool, themeSetting: string} */
		$input = $args['input'];

		/** @var bool */
		$paused = $input['paused'];

		/** @var bool */
		$purge_on_uninstall = $input['purgeOnUninstall'];

		/** @var string */
		$theme_setting_value = $input['themeSetting'];
		$theme_setting       = ThemeSetting::from( $theme_setting_value ); // 値の妥当性を検証

		/** @var string|null */
		$scta_url_value = $input['sctaUrl'] ?? null;
		if ( $scta_url_value !== null ) {
			$home_url = $this->wordpress_property_provider->homeUrl();
			if ( ! Strings::starts_with( $scta_url_value, $home_url ) ) {
				throw new \InvalidArgumentException( '[57280AEF] sctaUrl must start with homeUrl.' );
			}
		}
		$scta_url = SctaUrl::fromNullable( $scta_url_value );

		return $this->transaction_service->transactional(
			function () use ( $paused, $scta_url, $purge_on_uninstall, $theme_setting ) {
				$this->paused_repository->save( $paused );
				$this->scta_url_repository->save( $scta_url );
				$this->purge_on_uninstall_repository->save( $purge_on_uninstall );
				$this->theme_setting_repository->save( $theme_setting );

				return true;
			}
		);
	}
}
