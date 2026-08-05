<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Repository;

use Cornix\Serendipity\Core\Domain\ValueObject\ThemeSetting;

interface ThemeSettingRepository {

	/** テーマ設定を取得します(未設定時はauto) */
	public function get(): ThemeSetting;

	/** テーマ設定を保存します */
	public function save( ThemeSetting $theme_setting ): void;
}
