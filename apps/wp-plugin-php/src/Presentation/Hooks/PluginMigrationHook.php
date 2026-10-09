<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\Exception\LockAcquisitionException;
use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Service\LockService;
use Baywall\Core\Application\Service\PluginDeactivationService;
use Baywall\Core\Application\Service\PluginMigrationService;
use Baywall\Core\Infrastructure\System\ArchitectureChecker;
use Baywall\Core\Infrastructure\System\PhpExtChecker;
use Baywall\Core\Presentation\Hooks\Base\HookBase;
use Baywall\Core\Infrastructure\WordPress\Database\Repository\WpInstalledPluginVersionRepository;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressVersionChecker;
use DI\Container;
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

	public function __construct( private readonly Container $container ) {}

	public function register(): void {
		// 優先度1で、他プラグインの初期化処理より前にマイグレーションを完了させる
		add_action( 'plugins_loaded', array( $this, 'addActionPluginsLoaded' ), 1 );
	}

	public function addActionPluginsLoaded(): void {
		try {
			/** @var PluginMigrationService */
			$plugin_migrate_service = $this->container->get( PluginMigrationService::class );

			// マイグレーション処理が不要な場合は処理抜け
			if ( ! $plugin_migrate_service->required() ) {
				return;
			}

			try {
				/** @var LockService */
				$lock_service = $this->container->get( LockService::class );

				// タイムアウト0で即時失敗させ、同時リクエスト時は先着のリクエストにマイグレーションを委譲する
				$lock_service->withLock(
					self::LOCK_NAME,
					function () use ( $plugin_migrate_service ) {
						// ロック待ちの間に他リクエストでマイグレーション済みだった場合の二重チェック
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
					}
				);
			} catch ( LockAcquisitionException $e ) {
				// ロック取得失敗（同時リクエスト）又はロック機構のエラー時は次リクエストに委譲する。
				// ロック機構のエラーはDBの一次的な障害と考えられるため、プラグインの無効化までは行わない。
				/** @var AppLogger */
				$this->container->get( AppLogger::class )->warn( '[CB148F01] Plugin migration skipped (lock not acquired): ' . $e->getMessage() );
				return;
			}
		} catch ( Throwable $e ) {
			// 例外を記録（フロントエンドは画面出力が無いため、ここでの記録が唯一の手がかり）
			/** @var AppLogger */
			$this->container->get( AppLogger::class )->error( $e );

			// アップデートに失敗した場合はプラグインを無効化
			$this->deactivatePlugin();

			/** @var WordPressPropertyProvider */
			$wp_property_provider = $this->container->get( WordPressPropertyProvider::class );
			if ( ! $wp_property_provider->isAdmin() ) {
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
		/** @var PluginDeactivationService */
		$plugin_deactivation_service = $this->container->get( PluginDeactivationService::class );
		$plugin_deactivation_service->deactivatePlugin();
	}
}
