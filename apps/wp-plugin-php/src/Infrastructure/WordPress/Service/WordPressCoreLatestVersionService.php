<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

/**
 * WordPressコアの最新マイナーバージョン一覧を取得するサービスのインタフェース。
 *
 * WordPress公式API(`https://api.wordpress.org/core/version-check/1.7/`)が提供する
 * 最新マイナーバージョン一覧を基準に、現在のWordPressバージョンがサポート対象かどうかを
 * 判定するために使用します。
 *
 * 実装クラス:
 * - `LocalWordPressCoreLatestVersionService`: ローカルJSONファイルから取得
 * - `WpWordPressCoreLatestVersionService`: REST APIから取得(失敗時ローカルJSONにフォールバック)
 */
interface WordPressCoreLatestVersionService {

	/**
	 * WordPressコアの最新マイナーバージョン一覧を返します。
	 *
	 * @return string[] 例: ['7.0.2', '6.9.5', '6.8.6', ...]
	 */
	public function getVersions(): array;
}
