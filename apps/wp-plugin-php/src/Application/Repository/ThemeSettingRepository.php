<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Repository;

use Baywall\Core\Domain\ValueObject\ThemeSetting;

interface ThemeSettingRepository {

	/** テーマ設定を取得します(未設定時はauto) */
	public function get(): ThemeSetting;

	/** テーマ設定を保存します */
	public function save( ThemeSetting $theme_setting ): void;
}
