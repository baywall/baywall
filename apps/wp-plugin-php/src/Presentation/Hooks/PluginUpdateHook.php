<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\UseCase\Migrate;
use Cornix\Serendipity\Core\Infrastructure\System\ArchitectureChecker;
use Cornix\Serendipity\Core\Infrastructure\System\PhpExtChecker;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\OptionGateway\PluginVersionOption;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PluginInfoProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;
use Psr\Container\ContainerInterface;
use Throwable;

// ■プラグインがインストールされた時や更新時のhookに関して
// - `update_plugins_{$host_name}`
// 　-> WP5.8.0以降で使用可能。2025/7/25にWPの最低バージョンを5.8に更新したが、現時点では未使用
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
			$plugin_version_option = $this->container->get( PluginVersionOption::class );
			$plugin_info           = $this->container->get( PluginInfoProvider::class );
			// バージョンチェック
			$prev_installed_version = $plugin_version_option->get() ?? '0.0.0';
			$to_version             = $plugin_info->version();
			if ( version_compare( $prev_installed_version, $to_version, '<' ) ) {
				// 動作環境のチェック
				$this->checkSystem();

				// マイグレーション実行
				$this->container->get( Migrate::class )->handle( $prev_installed_version, $to_version );

				// プラグインのバージョンを更新
				$plugin_version_option->update( $to_version, false );
			}
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
	 */
	private function checkSystem(): void {
		// 64ビットのPHP環境であることを確認
		$this->container->get( ArchitectureChecker::class )->checkIs64bit();

		// PHP拡張のチェック
		$this->container->get( PhpExtChecker::class )->checkPhpExtensions();

		// マルチサイト構成でないことを確認
		if ( $this->container->get( WordPressPropertyProvider::class )->isMultisite() ) {
			throw new \RuntimeException( '[CFE0F8E3] This plugin does not support WordPress Multisite.' );
		}
	}

	private function deactivatePlugin(): void {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// プラグインを無効化
		deactivate_plugins( plugin_basename( $this->container->get( PluginInfoProvider::class )->mainFilePath() ) );
	}
}
