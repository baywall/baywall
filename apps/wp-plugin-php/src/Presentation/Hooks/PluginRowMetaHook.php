<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\Port\PluginAutoUpdateStateProvider;
use Baywall\Core\Application\Service\EnsRpcUrlResolver;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginInfoProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpEnvironment;
use Baywall\Core\Presentation\Hooks\Base\HookBase;

/**
 * プラグイン一覧に注意メッセージを表示するフック。
 *
 * 本番環境で自動アップデートが有効なのにEthereum MainnetのRPC URLが未登録の場合、
 * 自プラグインの行にRPC URL登録を促すメッセージを表示します。
 * 表示処理の例外でプラグイン一覧を壊さないよう、全例外は`\Throwable`で捕捉して素通しします。
 */
class PluginRowMetaHook extends HookBase {
	/**
	 * @param \Closure(): WpPluginInfoProvider          $plugin_info_provider
	 * @param \Closure(): WpEnvironment                 $wp_environment
	 * @param \Closure(): PluginAutoUpdateStateProvider $auto_update_state_provider
	 * @param \Closure(): EnsRpcUrlResolver             $ens_rpc_url_resolver
	 */
	public function __construct(
		private readonly \Closure $plugin_info_provider,
		private readonly \Closure $wp_environment,
		private readonly \Closure $auto_update_state_provider,
		private readonly \Closure $ens_rpc_url_resolver,
	) {}

	public function register(): void {
		add_filter( 'plugin_row_meta', array( $this, 'filterPluginRowMeta' ), 10, 4 );
	}

	/**
	 * `plugin_row_meta`フィルターのコールバック。
	 *
	 * @param string[]             $plugin_meta 行のメタ情報
	 * @param string               $plugin_file プラグインのベース名
	 * @param array<string, mixed> $plugin_data プラグインヘッダ情報
	 * @param string               $status      ステータス（all / active / inactive等）
	 * @return string[] メッセージ追加済みの行のメタ情報
	 */
	public function filterPluginRowMeta( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ) {
		try {
			$plugin_info_provider = ( $this->plugin_info_provider )();
			if ( $plugin_file !== plugin_basename( $plugin_info_provider->mainFilePath() ) ) {
				return $plugin_meta;
			}

			// 非本番環境では更新チェック自体を行わないため、メッセージは誤誘導になる（PluginUpdateCheckHookと同一条件）
			$wp_environment = ( $this->wp_environment )();
			if ( ! $wp_environment->isProduction() ) {
				return $plugin_meta;
			}

			$auto_update_state_provider = ( $this->auto_update_state_provider )();
			if ( ! $auto_update_state_provider->isAutoUpdateEnabled() ) {
				return $plugin_meta;
			}

			$rpc_url = ( $this->ens_rpc_url_resolver )()->resolve();
			if ( ! is_null( $rpc_url ) ) {
				return $plugin_meta;
			}

			$plugin_meta[] = esc_html__( 'To enable automatic updates, you need to register an RPC URL for Ethereum Mainnet.', 'baywall' );
		} catch ( \Throwable $e ) {
			// Do nothing: 表示処理の失敗でプラグイン一覧を壊さない
		}
		return $plugin_meta;
	}
}
