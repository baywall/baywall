<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\ThemeSettingRepository;

class ResolveThemeSetting {

	private ThemeSettingRepository $theme_setting_repository;

	public function __construct( ThemeSettingRepository $theme_setting_repository ) {
		$this->theme_setting_repository = $theme_setting_repository;
	}

	public function handle( array $root_value, array $args ): string {
		// アクセス制御は不要
		return $this->theme_setting_repository->get()->value();
	}
}
