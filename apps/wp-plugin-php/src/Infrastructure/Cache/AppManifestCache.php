<?php
declare(strict_types=1);
namespace Baywall\Core\Infrastructure\Cache;

interface AppManifestCache {
	/** app-manifest.jsonのJSON文字列をキャッシュとして保存します */
	public function set( string $manifest_json ): void;

	/** キャッシュされたapp-manifest.jsonのJSON文字列を取得します。キャッシュがない場合はnullを返します */
	public function get(): ?string;
}
