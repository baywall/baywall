<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Plugin;

use Baywall\Core\Application\Port\PluginAutoUpdateStateProvider;

/**
 * WordPress上でプラグインの自動アップデート状態を判定するクラス。
 *
 * WordPressコア（`WP_Automatic_Updater::should_update()` / `WP_Plugins_List_Table`）と同じ判定経路で、
 * 自プラグインが自動アップデートされる状態にあるかを判定します。
 */
class WpPluginAutoUpdateStateProvider implements PluginAutoUpdateStateProvider {
	public function __construct( private readonly WpPluginInfoProvider $plugin_info_provider ) {}

	/** @inheritdoc */
	public function isAutoUpdateEnabled(): bool {
		// `wp_is_auto_update_*`はwp-admin/includes/update.phpに定義されるため、cron等の未読込経路に備えて防御的にロードする
		if ( ! function_exists( 'wp_is_auto_update_enabled_for_type' ) ) {
			// @phpstan-ignore requireOnce.fileNotFound, requireOnce.fileNotFound
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		// 1. 自動更新基盤の有効性（強制オフ・AUTOMATIC_UPDATER_DISABLED等を反映）
		if ( ! wp_is_auto_update_enabled_for_type( 'plugin' ) ) {
			return false;
		}

		$plugin_basename = plugin_basename( $this->plugin_info_provider->mainFilePath() );

		// 2. 個別設定（自動更新を許可したプラグイン一覧）
		$enabled = in_array( $plugin_basename, (array) get_site_option( 'auto_update_plugins', array() ), true );

		// 3. ホスティング等による強制（強制 ON / OFF 両方）があれば2の結果を上書き
		/** @var null|bool スタブの @return bool と異なり、第二引数の null（フィルタ未介入）がそのまま返り得る */
		$forced = wp_is_auto_update_forced_for_item( 'plugin', null, $this->filterPayload( $plugin_basename ) );
		return is_null( $forced ) ? $enabled : $forced;
	}

	/**
	 * `auto_update_plugin`フィルタへ渡す更新オファーのペイロードを構築します。
	 *
	 * コアのプラグイン一覧と同じく全プロパティにデフォルト値を入れ、
	 * `update_plugins`サイトトランジェントの自プラグインのofferがあればそれとマージします。
	 */
	private function filterPayload( string $plugin_basename ): \stdClass {
		$filter_payload = array(
			'id'            => $plugin_basename,
			'slug'          => '',
			'plugin'        => $plugin_basename,
			'new_version'   => '',
			'url'           => '',
			'package'       => '',
			'icons'         => array(),
			'banners'       => array(),
			'banners_rtl'   => array(),
			'tested'        => '',
			'requires_php'  => '',
			'compatibility' => new \stdClass(),
		);

		$plugin_info = get_site_transient( 'update_plugins' );
		if ( isset( $plugin_info->response[ $plugin_basename ] ) ) {
			$filter_payload = wp_parse_args( (array) $plugin_info->response[ $plugin_basename ], $filter_payload );
		} elseif ( isset( $plugin_info->no_update[ $plugin_basename ] ) ) {
			$filter_payload = wp_parse_args( (array) $plugin_info->no_update[ $plugin_basename ], $filter_payload );
		}

		return (object) $filter_payload;
	}
}
