<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Constant\Config;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Baywall\Core\Infrastructure\WordPress\Service\WpAppManifestFetcher;
use Baywall\Core\Infrastructure\WordPress\Service\WpEnvironment;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginInfoProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginPackageChecksumVerifier;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginUpdateChecker;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;
use Baywall\Core\Presentation\Hooks\Base\HookBase;
use Psr\Container\ContainerInterface;

/**
 * プラグインの自動アップデートチェックを行うフック。
 *
 * WordPress 5.8+ 標準の `Update URI` ヘッダ + `update_plugins_{host}` フィルター方式を使用し、
 * manifest(`Config::APP_MANIFEST_URL`)の情報を元に更新情報を管理画面の標準プラグイン一覧に載せる。
 *
 * 本番環境のみで動作する（開発・テスト環境では更新チェック自体を行わない）。
 * いかなる例外も管理画面の表示障害にしないため、全例外は `\Throwable` で捕捉して `false` を返す（フェイルセーフ）。
 */
class PluginUpdateCheckHook extends HookBase {

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}
	private ContainerInterface $container;

	/**
	 * フィルターフック名（update_plugins_{host}）を`Config::UPDATE_URI`のホスト名から導出します。
	 * 定数とフック名の不整合を構造的に防ぐため、リテラルのフック名は定義しない。
	 */
	public static function updatePluginsFilterName(): string {
		$host = wp_parse_url( Config::UPDATE_URI, PHP_URL_HOST );
		assert( is_string( $host ) && '' !== $host, '[87E12F57] UPDATE_URI must contain a valid hostname.' );
		return 'update_plugins_' . $host;
	}

	public function register(): void {
		add_filter( self::updatePluginsFilterName(), array( $this, 'filterUpdatePlugins' ), 10, 4 );
		// 更新zipのダウンロード前にsha256チェックサム検証を行う（fail-closed）
		add_filter( 'upgrader_pre_download', array( $this, 'filterUpgraderPreDownload' ), 10, 4 );
	}

	/**
	 * `upgrader_pre_download`フィルターのコールバック。
	 *
	 * 本プラグインの更新zipについてsha256チェックサム検証を行う(fail-closed)。
	 * 対象外の更新では`false`を返し、コアの通常ダウンロード処理に委ねる。
	 *
	 * @param mixed        $reply      先に登録されたフィルタの応答(false以外ならそれを尊重する)
	 * @param string       $package    パッケージのURL
	 * @param \WP_Upgrader $upgrader   アップグレーダーのインスタンス
	 * @param array        $hook_extra 追加引数
	 * @return mixed|false|string|\WP_Error 対象外はfalse、検証成功時は一時ファイルパス、検証失敗時はWP_Error
	 */
	public function filterUpgraderPreDownload( $reply, string $package, \WP_Upgrader $upgrader, array $hook_extra ) {
		// 先に登録されたフィルタが応答済みならその判断を尊重する
		if ( false !== $reply ) {
			return $reply;
		}

		try {
			/** @var WpPluginPackageChecksumVerifier */
			$checksum_verifier = $this->container->get( WpPluginPackageChecksumVerifier::class );
			return $checksum_verifier->verifyPreDownload( $package, $upgrader, $hook_extra );
		} catch ( \Throwable $e ) {
			// fail-closed: 検証を継続できない場合は更新を中止する（`false`で素通しすると未検証のまま展開されるため許容しない）
			$this->logUpdateCheckFailure( $e, LogLevel::error(), '[6F36FC04]' );
			return new \WP_Error( 'baywall_checksum_verification_failed', '[6F36FC04] An exception occurred during checksum verification: ' . $e->getMessage() );
		}
	}

	/**
	 * `update_plugins_{host}`フィルターのコールバック。
	 *
	 * @param mixed  $update      コアから渡される既存の更新情報（通常はfalse）
	 * @param array  $plugin_data プラグインヘッダ情報
	 * @param string $plugin_file プラグインベース名
	 * @param array  $locales     インストール済みロケール一覧
	 * @return array|false 更新提示時はペイロード配列、それ以外はfalse
	 */
	public function filterUpdatePlugins( $update, array $plugin_data, string $plugin_file, array $locales ) {
		// 本番環境限定。開発・テスト環境では更新チェック自体を行わない
		/** @var WpEnvironment */
		$wp_environment = $this->container->get( WpEnvironment::class );
		if ( ! $wp_environment->isProduction() ) {
			return false;
		}

		try {
			/** @var WpAppManifestFetcher */
			$manifest_fetcher = $this->container->get( WpAppManifestFetcher::class );
			$channel          = $manifest_fetcher->fetch();

			/** @var WpPluginInfoProvider */
			$plugin_info_provider = $this->container->get( WpPluginInfoProvider::class );
			/** @var WordPressPropertyProvider */
			$wp_property_provider = $this->container->get( WordPressPropertyProvider::class );

			/** @var WpPluginUpdateChecker */
			$update_checker = $this->container->get( WpPluginUpdateChecker::class );
			$response       = $update_checker->buildUpdateResponse(
				$channel,
				$plugin_info_provider->version(),
				$wp_property_provider->wpVersion(),
				PHP_VERSION
			);

			return is_array( $response ) ? $response : false;
		} catch ( \Throwable $e ) {
			// 更新非提示として扱い、管理画面を壊さない（フェイルセーフ）
			$this->logUpdateCheckFailure( $e, LogLevel::warn(), '[E89752F5]' );
			return false;
		}
	}

	/**
	 * 更新チェックの失敗をログに記録します。
	 *
	 * ログ記録自体が失敗してもフェイルセーフの判断を阻害しないよう、例外は捕捉して無視します。
	 *
	 * @param \Throwable $e             発生した例外
	 * @param LogLevel   $log_level     出力するログレベル
	 * @param string     $id            8桁HEX識別子
	 */
	private function logUpdateCheckFailure( \Throwable $e, LogLevel $log_level, string $id ): void {
		try {
			/** @var AppLogger */
			$app_logger = $this->container->get( AppLogger::class );
			$message    = "[{$id}] {$e->getMessage()}";
			if ( LogLevel::warn()->name() === $log_level->name() ) {
				$app_logger->warn( $message );
			} else {
				$app_logger->error( $message );
			}
		} catch ( \Throwable $log_exception ) {
			// Do nothing: ログ記録失敗によりフェイルセーフ判断を阻害しない
		}
	}
}
