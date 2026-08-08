<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Service\PluginMigrationService;
use Cornix\Serendipity\Core\Infrastructure\System\ArchitectureChecker;
use Cornix\Serendipity\Core\Infrastructure\System\PhpExtChecker;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpInstalledPluginVersionRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpPluginInfoProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WordPressVersionChecker;
use Psr\Container\ContainerInterface;
use Throwable;

// ■プラグインがインストールされた時や更新時のhookに関して
// - `update_plugins_{$host_name}`
// 　-> WP5.8.0以降で使用可能。2025/7/25にWPの最低バージョンを5.8に更新済みで、
// 　   現在は `PluginUpdateCheckHook` でプラグインの自動アップデートチェックに使用中
// 　   https://wordpress.stackexchange.com/a/419585
// - `plugins_loaded`, `init`
// 　-> FTPやSVNでプラグインを更新した場合でも検知できるが、フロントエンドを含む全てのページで実行される欠点あり
// - `register_activation_hook`
// 　- ユーザーがプラグインをアクティブにした時のみ実行され、プラグインアップグレード後には呼び出されない旨の情報あり(2012年時点の情報)
// 　  以下のURLでは`register_activation_hook`で現在のバージョンを`wp_options`に保存し、管理ページ読み込み時に都度バージョンを比較することを推奨している
// 　  https://wordpress.stackexchange.com/a/39828
// 　- マルチサイト環境の場合は`admin_init`を使用した方が良い(2011年時点の情報)
// 　  https://core.trac.wordpress.org/ticket/14170#comment:68
// ■プラグインアップグレード前のhookに関して
// - `upgrader_pre_install`を使用(`upgrader_process_complete`は使用しない)
// 　https://stackoverflow.com/a/56179550
// ■その他注意事項
// - マルチサイトの場合、他のサイトに対しても処理が実行されるかどうか確認する必要あり(もしくはサイトIDに依存しない設計にする)

class PluginUpdateHook extends HookBase {

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}
	private ContainerInterface $container;

	public function register(): void {
		add_action( 'admin_init', array( $this, 'addActionAdminInit' ) );
	}

	public function addActionAdminInit(): void {
		assert( is_admin() );
		try {
			/** @var PluginMigrationService */
			$plugin_migrate_service = $this->container->get( PluginMigrationService::class );

			// マイグレーション処理が不要な場合は処理抜け
			if ( ! $plugin_migrate_service->required() ) {
				return;
			}

			// 初回インストールかどうか（前回保存バージョンが null の場合を初回インストールとみなす）
			/** @var WpInstalledPluginVersionRepository */
			$wp_installed_plugin_version_repository = $this->container->get( WpInstalledPluginVersionRepository::class );
			$is_first_install                       = $this->isFirstInstall( $wp_installed_plugin_version_repository );

			// 動作環境のチェック（環境チェック一式は初回・更新時とも実行し、WordPressのバージョンチェックは初回インストール時のみ実行）
			$this->checkSystem( $is_first_install );

			// マイグレーション実行
			$plugin_migrate_service->migrate();

		} catch ( Throwable $e ) {
			// アップデートに失敗した場合はプラグインを無効化
			$this->deactivatePlugin();
			// wp_redirect( admin_url( 'plugins.php' ) ); // プラグイン一覧ページにリダイレクト

			// エラー内容を画面に表示して終了
			wp_die( (string) $e, '', array( 'back_link' => true ) );
		}
	}

	/**
	 * 動作環境のチェックを行います
	 *
	 * @param bool $check_wordpress_version WordPressのバージョンチェックを実行するかどうか（初回インストール時のみ true）
	 */
	private function checkSystem( bool $check_wordpress_version ): void {
		// 64ビットのPHP環境であることを確認
		/** @var ArchitectureChecker */
		$architecture_checker = $this->container->get( ArchitectureChecker::class );
		$architecture_checker->checkIs64bit( PHP_INT_SIZE );

		// PHP拡張のチェック
		/** @var PhpExtChecker */
		$php_ext_checker = $this->container->get( PhpExtChecker::class );
		$php_ext_checker->checkPhpExtensions();

		// マルチサイト構成でないことを確認
		/** @var WordPressPropertyProvider */
		$wp_property_provider = $this->container->get( WordPressPropertyProvider::class );
		if ( $wp_property_provider->isMultisite() ) {
			throw new \RuntimeException( '[CFE0F8E3] This plugin does not support WordPress Multisite.' );
		}

		// WordPressバージョンのチェックは初回インストール時のみ実行する。
		// プラグイン更新時は、プラグインの自動更新機能(PluginUpdateCheckHook)導入以降、
		// 更新のたびにWordPressコアのバージョンチェックが走るのは不適切なため、初回インストール時に限定する。
		if ( $check_wordpress_version ) {
			/** @var WordPressVersionChecker */
			$wp_version_checker = $this->container->get( WordPressVersionChecker::class );
			$wp_version_checker->checkVersion( $wp_property_provider->wpVersion() );
		}
	}

	/**
	 * 初回インストールかどうかを判定します。
	 *
	 * 前回保存されているプラグインバージョンが null（未保存）の場合を初回インストールとみなします。
	 *
	 * @param WpInstalledPluginVersionRepository $repository インストール済みプラグインバージョンのリポジトリ
	 */
	private function isFirstInstall( WpInstalledPluginVersionRepository $repository ): bool {
		return $repository->get() === null;
	}

	private function deactivatePlugin(): void {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// プラグインを無効化
		/** @var WpPluginInfoProvider */
		$plugin_info_provider = $this->container->get( WpPluginInfoProvider::class );
		deactivate_plugins( plugin_basename( $plugin_info_provider->mainFilePath() ) );
	}
}
