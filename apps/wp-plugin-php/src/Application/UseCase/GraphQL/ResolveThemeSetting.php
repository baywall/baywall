<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Repository\ThemeSettingRepository;

class ResolveThemeSetting {


	public function __construct( private readonly ThemeSettingRepository $theme_setting_repository ) {}

	public function handle( array $root_value, array $args ): string {
		// アクセス制御は不要
		return $this->theme_setting_repository->get()->value();
	}
}
