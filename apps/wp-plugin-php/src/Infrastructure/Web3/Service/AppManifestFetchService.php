<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\ValueObject\RpcUrl;

/**
 * app-manifest.json の内容（JSON文字列）を返すサービス。
 */
interface AppManifestFetchService {
	/**
	 * app-manifest.json を取得しJSON文字列を返します。
	 *
	 * @param RpcUrl $rpc_url ENS解決に使用するRPCエンドポイント
	 * @throws \RuntimeException manifestの取得に失敗した場合
	 */
	public function fetch( RpcUrl $rpc_url ): string;
}
