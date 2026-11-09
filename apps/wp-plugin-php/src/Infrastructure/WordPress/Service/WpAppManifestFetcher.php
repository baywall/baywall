<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\ValueObject\PluginUpdateChannel;
use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Service\AppManifestFetchService;
use Baywall\Core\Infrastructure\Web3\ValueObject\AppManifestData;

/**
 * app-manifest.json から設定されたチャンネルのリリースチャンネル情報を返すサービス。
 *
 * manifest の取得は `AppManifestFetchService` が担い、パースは `AppManifestData` が担い、
 * 本クラスは `Config::PLUGIN_UPDATE_CHANNEL_NAME` のチャンネル抽出のみを行います。
 *
 * 本クラス自体はキャッシュせず、チェックのたびに都度取得しますが、
 * `AppManifestFetchService` の差し替えにより実体はキャッシュ経由になります。
 * 取得・検証に失敗した場合は `\RuntimeException` を送出します（呼び出し側で「更新非提示」として扱います）。
 */
class WpAppManifestFetcher {
	public function __construct( private readonly AppManifestFetchService $app_manifest_fetch_service ) {}

	/**
	 * app-manifest.json を取得し、設定されたチャンネルのリリースチャンネル情報を返します。
	 *
	 * @param RpcUrl $rpc_url ENS解決に使用するRPCエンドポイント
	 * @throws \RuntimeException manifestの取得・検証に失敗した場合
	 */
	public function fetch( RpcUrl $rpc_url ): PluginUpdateChannel {
		$manifest_json = $this->app_manifest_fetch_service->fetch( $rpc_url );
		$channels      = ( new AppManifestData( $manifest_json ) )->channels();
		$channel_name  = Config::PLUGIN_UPDATE_CHANNEL_NAME;
		if ( ! isset( $channels[ $channel_name ] ) ) {
			throw new \RuntimeException( "[56F01B11] app-manifest.json is missing the '{$channel_name}' channel entry." );
		}
		return $channels[ $channel_name ];
	}
}
