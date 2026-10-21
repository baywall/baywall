<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\OptionRepository;

use Baywall\Core\Application\Repository\ThemeSettingRepository;
use Baywall\Core\Domain\ValueObject\ThemeSetting;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;
use Baywall\Core\Infrastructure\WordPress\Database\OptionGateway\Option\StringOption;

/**
 * テーマ設定を取得または保存するクラス
 */
class WpThemeSettingRepository implements ThemeSettingRepository {

	private readonly StringOption $option;

	public function __construct() {
		$this->option = new StringOption( WpOptionName::THEME );
	}

	/** テーマ設定を取得します(未設定時はauto) */
	public function get(): ThemeSetting {
		$theme = $this->option->get();
		if ( $theme === null ) {
			// 未設定時はデフォルトのautoを返す
			return ThemeSetting::auto();
		}
		return ThemeSetting::from( $theme );
	}

	/** テーマ設定を保存します */
	public function save( ThemeSetting $theme_setting ): void {
		$this->option->update( $theme_setting->value() );
	}
}
