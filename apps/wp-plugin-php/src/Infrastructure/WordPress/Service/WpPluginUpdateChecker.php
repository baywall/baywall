<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Composer\Semver\Comparator;
use Cornix\Serendipity\Core\Application\ValueObject\PluginUpdateChannel;
use Cornix\Serendipity\Core\Constant\Config;

/**
 * プラグインの更新可否を判定し、コアの`update_plugins_{host}`フィルターに渡すペイロードを構築するサービス。
 *
 * 判定順序:
 * 1. manifestのバージョンがインストール済みバージョンより大きいこと
 * 2. 現在WordPressバージョンが`requiresWordPress`以上であること
 * 3. 現在PHPバージョンが`requiresPhp`以上であること
 *
 * 全てを満たす場合のみペイロード配列を返し、それ以外はnull（更新非提示）を返します。
 */
class WpPluginUpdateChecker {

	/**
	 * 更新可否を判定し、コアが期待する更新情報ペイロード配列を構築します。
	 *
	 * @param PluginUpdateChannel $channel             manifestから取得したリリースチャンネル情報
	 * @param string              $installed_version   インストール済みプラグインバージョン
	 * @param string              $current_wp_version  現在のWordPressバージョン
	 * @param string              $current_php_version 現在のPHPバージョン
	 * @return array|null 更新提示時はペイロード配列、更新不要（または要件未達）の場合はnull
	 */
	public function buildUpdateResponse(
		PluginUpdateChannel $channel,
		string $installed_version,
		string $current_wp_version,
		string $current_php_version
	): ?array {
		// manifestのバージョンがインストール済みより新しい場合のみ更新対象
		// ※ semverとして解釈不能なバージョン文字列が来た場合はComparatorが例外を投げるため、
		// 呼び出し側（フック）で\Throwableを捕捉して更新非提示に倒す
		if ( ! Comparator::greaterThan( $channel->version(), $installed_version ) ) {
			return null;
		}

		// WordPressの要件チェック
		if ( ! Comparator::greaterThanOrEqualTo( $current_wp_version, $channel->requiresWordPress() ) ) {
			return null;
		}

		// PHPの要件チェック
		if ( ! Comparator::greaterThanOrEqualTo( $current_php_version, $channel->requiresPhp() ) ) {
			return null;
		}

		return array(
			// コアは`version`フィールドが必須（無いと更新情報として無視される）
			'version'      => $channel->version(),
			'new_version'  => $channel->version(),
			// 「詳細を見る」のリンク先。`slug`は意図的に含めない（含めると詳細モーダルがwordpress.orgへ遷移するため）
			'url'          => Config::UPDATE_URI,
			// 更新用zipのURL(`package`が空だと「自動更新は利用できない」表示となるため必須)
			'package'      => $channel->url(),
			'requires'     => $channel->requiresWordPress(),
			'requires_php' => $channel->requiresPhp(),
		);
	}
}
