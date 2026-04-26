<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

/**
 * GraphQL関連の設定値を提供するインタフェース
 */
interface GraphQLService {
	/** GraphQLスキーマファイルへのパスを取得 */
	public function getSchemaFilePath(): string;

	/** GraphQLスキーマのキャッシュファイルへのパスを取得 */
	public function getCacheFilePath(): string;
}
