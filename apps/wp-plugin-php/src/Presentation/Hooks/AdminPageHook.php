<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpConfig;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\HandleNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\I18nTextProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpPluginInfoProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\SlugProvider;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use Cornix\Serendipity\Core\Presentation\Hooks\Service\PhpVarExporter;
use Psr\Container\ContainerInterface;

class AdminPageHook extends HookBase {

	private ContainerInterface $container;

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	public function register(): void {
		// 管理画面のメニュー追加。
		add_action( 'admin_menu', array( $this, 'addActionAdminMenu' ) );

		// 管理画面のスクリプト読み込み。
		add_action( 'admin_enqueue_scripts', array( $this, 'addActionAdminEnqueueScripts' ) );

		// プラグイン一覧のアクションリンクに`設定`を追加。
		$plugin_file_path = realpath( __DIR__ . '/../../../baywall.php' );
		if ( $plugin_file_path === false ) {
			throw new \RuntimeException( '[B3E5333F] Failed to resolve plugin file path.' );
		}
		$hook_name = 'plugin_action_links_' . plugin_basename( $plugin_file_path );
		add_filter( $hook_name, array( $this, 'addFilterPluginActionLinks' ) );
	}

	public function addActionAdminMenu(): void {
		assert( is_admin() );

		$i18n          = $this->container->get( I18nTextProvider::class );
		$slug_provider = $this->container->get( SlugProvider::class );

		$capability    = 'manage_options'; // ユーザー権限(`manage_options`は、管理画面の`設定`へアクセス可能な権限)
		$page_callback = function () {
			$div_id = WpConfig::ADMIN_ROOT_DIV_ID;
			echo '<div id="' . esc_attr( $div_id ) . '"></div>';
		};

		// トップレベルメニュー追加
		add_menu_page(
			$i18n->pluginName(),    // メニューが表示された際のページのタイトルタグに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$i18n->pluginName(),    // 管理画面のメニューに表示されるテキスト
			$capability,            // ユーザー権限
			$slug_provider->adminMenuRoot(), // メニューのスラッグ
			$page_callback,
			'dashicons-admin-generic',  // メニューに表示されるアイコン
		);
	}

	/**
	 * 管理画面で使用するスクリプトを読み込みます
	 */
	public function addActionAdminEnqueueScripts(): void {
		assert( is_admin() );

		$handle_name_provider = $this->container->get( HandleNameProvider::class );
		$php_var_exporter     = $this->container->get( PhpVarExporter::class );
		$plugin               = $this->container->get( WpPluginInfoProvider::class );

		// 管理画面用のスクリプトを登録する際のハンドル名を取得
		$handle_name = $handle_name_provider->adminScript();

		// アセットファイルを読み込む
		$asset_file = include WpConfig::ADMIN_ASSET_PATH;

		// 管理画面のスクリプト読み込み
		wp_enqueue_script(
			$handle_name,
			$plugin->toUrl( WpConfig::ADMIN_JS_RELATIVE_PATH ),
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

		$i18n          = $this->container->get( I18nTextProvider::class );
		$slug_provider = $this->container->get( SlugProvider::class );
		$settings_url  = admin_url( 'admin.php?page=' . $slug_provider->adminMenuRoot() );

		$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html( $i18n->settings() ) . '</a>';
		return array_merge( array( 'settings' => $settings_link ), $actions );
	}
}
