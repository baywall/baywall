<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\PluginDeactivationService;

/** WordPress上でプラグインを無効化するクラス */
class WpPluginDeactivationService implements PluginDeactivationService {

	public function __construct( private readonly WpPluginInfoProvider $plugin_info_provider ) {}

	/** @inheritdoc */
	public function deactivatePlugin(): void {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// プラグインを無効化
		// ※ 現在のリクエストは停止せず、DBの`active_plugins`を更新するのみ
		deactivate_plugins( plugin_basename( $this->plugin_info_provider->mainFilePath() ) );
	}
}
