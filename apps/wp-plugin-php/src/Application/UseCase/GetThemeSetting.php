<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Repository\ThemeSettingRepository;

class GetThemeSetting {
	public function __construct( private readonly ThemeSettingRepository $theme_setting_repository ) {}

	public function handle(): string {
		// アクセス制御は不要
		return $this->theme_setting_repository->get()->value();
	}
}
