<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\Exception\LockAcquisitionException;
use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Repository\InstalledPluginVersionRepository;
use Baywall\Core\Application\Service\LockService;
use Baywall\Core\Application\Service\PluginDeactivationService;
use Baywall\Core\Application\Service\PluginMigrationService;
use Baywall\Core\Application\Service\PluginCompatibilityChecker;
use Baywall\Core\Infrastructure\System\ArchitectureChecker;
use Baywall\Core\Infrastructure\System\PhpExtChecker;
use Baywall\Core\Presentation\Hooks\Base\HookBase;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;
use Throwable;

// ■プラグインがインストールされた時や更新時のhookに関して
// - `update_plugins_{$host_name}`
// 　-> WP5.8.0以降で使用可能。2025/7/25にWPの最低バージョンを5.8に更新済みで、
// 　   現在は `PluginUpdateCheckHook` でプラグインの自動アップデートチェックに使用中
// 　   https://wordpress.stackexchange.com/a/419585
// - `plugins_loaded`(採用)
// 　-> FTPやSVNでプラグインを更新した場合でも検知できる。
// 　   フロントエンドを含む全てのページで実行されるため、自動更新後に管理画面へアクセスされなくても
// 　   マイグレーションが実行される（排他制御により同時実行は防止する）
// - `register_activation_hook`
// 　-> ユーザーがプラグインをアクティブにした時のみ実行され、プラグインアップグレード後には呼び出されない旨の情報あり(2012年時点の情報)
// 　  以下のURLでは`register_activation_hook`で現在のバージョンを`wp_options`に保存し、管理ページ読み込み時に都度バージョンを比較することを推奨している
// 　  https://wordpress.stackexchange.com/a/39828
// ■プラグインアップグレード前のhookに関して
// - `upgrader_pre_install`を使用(`upgrader_process_complete`は使用しない)
// 　https://stackoverflow.com/a/56179550
// ■その他注意事項
// - マルチサイトの場合、他のサイトに対しても処理が実行されるかどうか確認する必要あり(もしくはサイトIDに依存しない設計にする)

class PluginMigrationHook extends HookBase {

	private const LOCK_NAME = '38FA0139'; // 排他制御用の適当な文字列

	/**
	 * @param \Closure(): PluginMigrationService           $plugin_migration_service
	 * @param \Closure(): LockService                      $lock_service
	 * @param \Closure(): InstalledPluginVersionRepository $installed_plugin_version_repository
	 * @param \Closure(): AppLogger                        $app_logger
	 * @param \Closure(): WordPressPropertyProvider        $wordpress_property_provider
	 * @param \Closure(): ArchitectureChecker              $architecture_checker
	 * @param \Closure(): PhpExtChecker                    $php_ext_checker
	 * @param \Closure(): PluginCompatibilityChecker       $plugin_compatibility_checker
	 * @param \Closure(): PluginDeactivationService        $plugin_deactivation_service
	 */
	public function __construct(
		private readonly \Closure $plugin_migration_service,
		private readonly \Closure $lock_service,
		private readonly \Closure $installed_plugin_version_repository,
		private readonly \Closure $app_logger,
		private readonly \Closure $wordpress_property_provider,
		private readonly \Closure $architecture_checker,
		private readonly \Closure $php_ext_checker,
		private readonly \Closure $plugin_compatibility_checker,
		private readonly \Closure $plugin_deactivation_service,
	) {}

	public function register(): void {
		// 優先度1で、他プラグインの初期化処理より前にマイグレーションを完了させる
		add_action( 'plugins_loaded', array( $this, 'addActionPluginsLoaded' ), 1 );
	}

	public function addActionPluginsLoaded(): void {
		try {
			$plugin_migration_service = ( $this->plugin_migration_service )();

			// マイグレーション処理が不要な場合は処理抜け
			if ( ! $plugin_migration_service->required() ) {
				return;
			}

			try {
				$lock_service = ( $this->lock_service )();

				// タイムアウト0で即時失敗させ、同時リクエスト時は先着のリクエストにマイグレーションを委譲する
				$lock_service->withLock(
					self::LOCK_NAME,
					function () use ( $plugin_migration_service ) {
						// ロック待ちの間に他リクエストでマイグレーション済みだった場合の二重チェック
						if ( ! $plugin_migration_service->required() ) {
							return;
						}

						// 初回インストールかどうか（前回保存バージョンが null の場合を初回インストールとみなす）
						$wp_installed_plugin_version_repository = ( $this->installed_plugin_version_repository )();
						$is_first_install                       = $this->isFirstInstall( $wp_installed_plugin_version_repository );

						// 動作環境のチェック（環境チェック一式は初回・更新時とも実行し、WordPress・PHPバージョンのサポートチェックは初回インストール時のみ実行）
						$this->checkSystem( $is_first_install );

						// マイグレーション実行
						$plugin_migration_service->migrate();
					}
				);
			} catch ( LockAcquisitionException $e ) {
				// ロック取得失敗（同時リクエスト）又はロック機構のエラー時は次リクエストに委譲する。
				// ロック機構のエラーはDBの一次的な障害と考えられるため、プラグインの無効化までは行わない。
				( $this->app_logger )()->warn( '[CB148F01] Plugin migration skipped (lock not acquired): ' . $e->getMessage() );
				return;
			}
		} catch ( Throwable $e ) {
			// 例外を記録（フロントエンドは画面出力が無いため、ここでの記録が唯一の手がかり）
			( $this->app_logger )()->error( $e );

			// アップデートに失敗した場合はプラグインを無効化
			$this->deactivatePlugin();

			$wordpress_property_provider = ( $this->wordpress_property_provider )();
			if ( ! $wordpress_property_provider->isAdmin() ) {
				// フロントエンドでは画面出力せず、現在のリクエストは継続させる
				// ※ 無効化はDBの`active_plugins`を更新するだけのため、次回リクエスト以降に有効になる
				return;
			}

			// エラー内容を画面に表示して終了
			// ※ 管理画面に表示されるため、詳細なスタックトレースは省略
			wp_die( $e->getMessage(), '', array( 'back_link' => true ) );
		}
	}

	/**
	 * 動作環境のチェックを行います
	 *
	 * @param bool $check_cms_version WordPress・PHPバージョンのサポートチェックを実行するかどうか（初回インストール時のみ true）
	 */
	private function checkSystem( bool $check_cms_version ): void {
		// 64ビットのPHP環境であることを確認
		$architecture_checker = ( $this->architecture_checker )();
		$architecture_checker->checkIs64bit( PHP_INT_SIZE );

		// PHP拡張のチェック
		$php_ext_checker = ( $this->php_ext_checker )();
		$php_ext_checker->checkPhpExtensions();

		// マルチサイト構成でないことを確認
		$wordpress_property_provider = ( $this->wordpress_property_provider )();
		if ( $wordpress_property_provider->isMultisite() ) {
			throw new \RuntimeException( '[CFE0F8E3] This plugin does not support WordPress Multisite.' );
		}

		// WordPress・PHPバージョンのサポートチェックは初回インストール時のみ実行する。
		// プラグイン更新時は、プラグインの自動更新機能(PluginUpdateCheckHook)導入以降、
		// 更新のたびにチェックが走るのは不適切なため、初回インストール時に限定する。
		if ( $check_cms_version ) {
			$plugin_compatibility_checker = ( $this->plugin_compatibility_checker )();

			$wp_version = $wordpress_property_provider->wpVersion();
			if ( ! $plugin_compatibility_checker->isSupportCmsVersion( $wp_version ) ) {
				throw new \RuntimeException( "[96BAB762] This plugin does not support WordPress version {$wp_version}." );
			}
			if ( ! $plugin_compatibility_checker->isSupportPhpVersion( PHP_VERSION ) ) {
				throw new \RuntimeException( '[1ED6C82B] This plugin does not support PHP version ' . PHP_VERSION . '.' );
			}
		}
	}

	/**
	 * 初回インストールかどうかを判定します。
	 *
	 * 前回保存されているプラグインバージョンが null（未保存）の場合を初回インストールとみなします。
	 *
	 * @param InstalledPluginVersionRepository $installed_plugin_version_repository インストール済みプラグインバージョンのリポジトリ
	 */
	private function isFirstInstall( InstalledPluginVersionRepository $installed_plugin_version_repository ): bool {
		return $installed_plugin_version_repository->get() === null;
	}

	private function deactivatePlugin(): void {
		$plugin_deactivation_service = ( $this->plugin_deactivation_service )();
		$plugin_deactivation_service->deactivatePlugin();
	}
}
