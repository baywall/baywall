<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginInfoProvider;
use Baywall\Core\Presentation\Hooks\Base\HookBase;

/** プラグインのテキストドメインをロードするクラス。 */
class I18nHook extends HookBase {

	/**
	 * @param \Closure(): WpPluginInfoProvider $plugin_info_provider
	 */
	public function __construct( private readonly \Closure $plugin_info_provider ) {}

	public function register(): void {
		add_action( 'init', array( $this, 'loadTextdomain' ) );
	}

	public function loadTextdomain(): void {
		// プラグイン同梱の languages/ は WP の自動探索対象外のため、明示的にロードする
		$plugin_info_provider = ( $this->plugin_info_provider )();
		load_plugin_textdomain( 'baywall', false, dirname( plugin_basename( $plugin_info_provider->mainFilePath() ) ) . '/languages' );
	}
}
