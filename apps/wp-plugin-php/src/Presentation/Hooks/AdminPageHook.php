<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Infrastructure\WordPress\Content\HandleNameProvider;
use Baywall\Core\Infrastructure\WordPress\I18n\I18nTextProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginAssetUrlProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginInfoProvider;
use Baywall\Core\Infrastructure\WordPress\Content\SlugProvider;
use Baywall\Core\Presentation\Hooks\Base\HookBase;
use Baywall\Core\Presentation\Hooks\Service\PhpVarExporter;

class AdminPageHook extends HookBase {
	/**
	 * @param \Closure(): I18nTextProvider         $i18n_text_provider
	 * @param \Closure(): SlugProvider             $slug_provider
	 * @param \Closure(): HandleNameProvider       $handle_name_provider
	 * @param \Closure(): PhpVarExporter           $php_var_exporter
	 * @param \Closure(): WpPluginAssetUrlProvider $asset_url_provider
	 */
	public function __construct(
		private readonly WpPluginInfoProvider $plugin_info_provider,
		private readonly \Closure $i18n_text_provider,
		private readonly \Closure $slug_provider,
		private readonly \Closure $handle_name_provider,
		private readonly \Closure $php_var_exporter,
		private readonly \Closure $asset_url_provider,
	) {}

	public function register(): void {
		// 管理画面のメニュー追加。
		add_action( 'admin_menu', array( $this, 'addActionAdminMenu' ) );

		// 管理画面のスクリプト読み込み。
		add_action( 'admin_enqueue_scripts', array( $this, 'addActionAdminEnqueueScripts' ) );

		// プラグイン一覧のアクションリンクに`設定`を追加。
		$hook_name = 'plugin_action_links_' . plugin_basename( $this->plugin_info_provider->mainFilePath() );
		add_filter( $hook_name, array( $this, 'addFilterPluginActionLinks' ) );
	}

	public function addActionAdminMenu(): void {
		assert( is_admin() );

		$i18n_text_provider = ( $this->i18n_text_provider )();
		$slug_provider      = ( $this->slug_provider )();

		$capability    = 'manage_options'; // ユーザー権限(`manage_options`は、管理画面の`設定`へアクセス可能な権限)
		$page_callback = function () {
			$div_id = WpConfig::ADMIN_ROOT_DIV_ID;
			echo '<div id="' . esc_attr( $div_id ) . '"></div>';
		};

		// トップレベルメニュー追加
		add_menu_page(
			$i18n_text_provider->pluginName(),    // メニューが表示された際のページのタイトルタグに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$i18n_text_provider->pluginName(),    // 管理画面のメニューに表示されるテキスト
			$capability,            // ユーザー権限
			$slug_provider->adminMenuRoot(), // メニューのスラッグ
			$page_callback,
			'dashicons-admin-generic',  // メニューに表示されるアイコン
		);
	}

	/**
	 * 管理画面で使用するスクリプトを読み込みます
	 *
	 * @param string $hook_suffix 現在の管理画面のフックサフィックス(`admin_enqueue_scripts`から第1引数で渡される)
	 */
	public function addActionAdminEnqueueScripts( string $hook_suffix ): void {
		assert( is_admin() );

		$slug_provider = ( $this->slug_provider )();

		// トップレベルページのフックサフィックスは`toplevel_page_<menu_slug>`（WPコア get_plugin_page_hookname の規約）。
		// 設定画面以外では管理画面用スクリプトを読み込まない。
		if ( 'toplevel_page_' . $slug_provider->adminMenuRoot() !== $hook_suffix ) {
			return;
		}

		$handle_name_provider = ( $this->handle_name_provider )();
		$php_var_exporter     = ( $this->php_var_exporter )();
		$asset_url_provider   = ( $this->asset_url_provider )();

		// 管理画面用のスクリプトを登録する際のハンドル名を取得
		$handle_name = $handle_name_provider->adminScript();

		// アセットファイルを読み込む
		$asset_file = include WpConfig::ADMIN_ASSET_PATH;

		// 管理画面のスクリプト読み込み
		wp_enqueue_script(
			$handle_name,
			$asset_url_provider->url( WpConfig::ADMIN_JS_RELATIVE_PATH ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true,   // フッターに出力。
		);

		// インラインスクリプトを追加
		$php_var_exporter->addInlineScript( $handle_name );
	}

	/**
	 * プラグイン一覧のアクションリンクに`設定`を追加します。
	 *
	 * @param string[] $actions
	 * @return string[]
	 */
	public function addFilterPluginActionLinks( array $actions ): array {
		// @see (example) https://developer.wordpress.org/reference/hooks/plugin_action_links_plugin_file/#more-information

		$i18n_text_provider = ( $this->i18n_text_provider )();
		$slug_provider      = ( $this->slug_provider )();
		$settings_url       = admin_url( 'admin.php?page=' . $slug_provider->adminMenuRoot() );

		$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html( $i18n_text_provider->settings() ) . '</a>';
		return array_merge( array( 'settings' => $settings_link ), $actions );
	}
}
