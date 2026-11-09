<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Cache\AppManifestCache;
use Baywall\Core\Infrastructure\Web3\Service\AppManifestFetchService;
use Baywall\Core\Infrastructure\Web3\Service\AppManifestFetchServiceImpl;
use Baywall\Core\Infrastructure\Web3\ValueObject\AppManifestData;

/**
 * AppManifest取得にtransientキャッシュを適用するサービス。
 */
class WpCachedAppManifestFetchService implements AppManifestFetchService {
	public function __construct(
		private readonly AppManifestFetchServiceImpl $app_manifest_fetch_service,
		private readonly AppManifestCache $app_manifest_cache
	) {}

	/**
	 * app-manifest.json を取得しJSON文字列を返します。
	 *
	 * キャッシュヒット時は `RpcUrl` を無視します（静的キーのため）。
	 *
	 * @param RpcUrl $rpc_url ENS解決に使用するRPCエンドポイント
	 * @throws \RuntimeException manifestの取得に失敗した場合
	 */
	public function fetch( RpcUrl $rpc_url ): string {
		$cached_json = null;
		try {
			$cached_json = $this->app_manifest_cache->get();
			if ( is_string( $cached_json ) ) {
				new AppManifestData( $cached_json ); // AppManifestData 変換チェック
				return $cached_json;
			}
		} catch ( \Throwable $e ) {
			// 万が一キャッシュ取得で例外が発生した場合でも後続処理を行うため
			// ここでは再スローしない
		}

		// 外部から manifest データを取得
		$manifest_json = $this->app_manifest_fetch_service->fetch( $rpc_url );

		try {
			new AppManifestData( $manifest_json ); // AppManifestData 変換チェック
			$this->app_manifest_cache->set( $manifest_json );
		} catch ( \Throwable $e ) {
			// キャッシュの保存に失敗しても再スローしない
		}

		return $manifest_json;
	}
}
