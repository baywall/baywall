<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Repository;

use Baywall\Core\Application\Repository\ThemeSettingRepository;
use Baywall\Core\Domain\ValueObject\ThemeSetting;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;

/**
 * テーマ設定を取得または保存するクラス
 */
class WpThemeSettingRepository implements ThemeSettingRepository {

	private string $option_name;

	public function __construct() {
		$this->option_name = WpOptionName::THEME;
	}

	/** テーマ設定を取得します(未設定時はauto) */
	public function get(): ThemeSetting {
		/** @var string|false */
		$theme = get_option( $this->option_name, false );
		if ( $theme === false || ! is_string( $theme ) ) {
			// 未設定時はデフォルトのautoを返す
			return ThemeSetting::auto();
		}
		return ThemeSetting::from( $theme );
	}

	/** テーマ設定を保存します */
	public function save( ThemeSetting $theme_setting ): void {
		update_option( $this->option_name, $theme_setting->value() );
	}
}
