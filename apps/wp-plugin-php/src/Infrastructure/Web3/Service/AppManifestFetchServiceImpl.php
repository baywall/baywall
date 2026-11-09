<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Client\HttpGetTransport;

/**
 * ENS テキストレコードから app-manifest.json の URL を解決し、取得してJSON文字列を返すサービス。
 *
 * 取得に失敗した場合は `\RuntimeException` を送出します。
 * JSON デコード・構造検証は含めず、取得したbodyをそのまま返します（`AppManifestData` の責務）。
 */
class AppManifestFetchServiceImpl implements AppManifestFetchService {
	public function __construct(
		private readonly EnsService $ens_service,
		private readonly HttpGetTransport $http_get_transport
	) {}

	/**
	 * app-manifest.json を取得しJSON文字列を返します。
	 *
	 * @param RpcUrl $rpc_url ENS解決に使用するRPCエンドポイント
	 * @throws \RuntimeException manifestの取得に失敗した場合
	 */
	public function fetch( RpcUrl $rpc_url ): string {
		$manifest_url = $this->ens_service->getText( $rpc_url, Config::APP_MANIFEST_ENS, Config::ENS_TEXT_FIELD_NAME_MANIFEST );
		if ( '' === $manifest_url ) {
			// URL未設定は取得失敗として扱う（呼び出し側のフェイルセーフに倒す）
			throw new \RuntimeException( '[842E73E7] manifest URL is not set in the ENS text record.' );
		}
		// 更新zipは任意コード実行に直結するため、平文取得を防ぐ目的でhttpsスキームのみ受け付ける
		// （`Url::checkValidUrlFormat`はhttp始まりしか検査しないため使用しない）
		$scheme = parse_url( $manifest_url, PHP_URL_SCHEME );
		if ( ! is_string( $scheme ) || 'https' !== strtolower( $scheme ) ) {
			throw new \RuntimeException( "[8812B2B0] manifest URL must use the https scheme. - {$manifest_url}" );
		}

		$response = $this->http_get_transport->get( $manifest_url, Config::APP_MANIFEST_REQUEST_TIMEOUT );

		// HTTPステータスコード200確認
		if ( 200 !== $response['status'] ) {
			throw new \RuntimeException( "[91E6B934] app-manifest.json returned HTTP status {$response['status']}." );
		}

		return $response['body'];
	}
}
