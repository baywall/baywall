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
 * 1. manifestのバージョンがインストール済み以下（更新なし）なら`no_update`用ペイロードを返す
 * 2. manifestのバージョンがインストール済みより新しく、かつ現在のWordPressバージョンが
 *    `requiresWordPress`以上、かつ現在のPHPバージョンが`requiresPhp`以上なら更新ペイロードを返す
 * 3. 更新はあるが要件未達の場合はnull（更新非提示）を返す
 *
 * 戻り値は「更新ペイロード / `no_update`用ペイロード / null」の3状態を取ります。
 */
class WpPluginUpdateChecker {

	/**
	 * 更新可否を判定し、コアが期待する更新情報ペイロード配列を構築します。
	 *
	 * @param PluginUpdateChannel $channel             manifestから取得したリリースチャンネル情報
	 * @param string              $installed_version   インストール済みプラグインバージョン
	 * @param string              $current_wp_version  現在のWordPressバージョン
	 * @param string              $current_php_version 現在のPHPバージョン
	 * @return array|null 戻り値は以下の3状態:
	 *                    - 更新ペイロード配列（更新あり・要件充足）
	 *                    - `no_update`用ペイロード配列（更新なし）
	 *                    - null（更新ありだが要件未達）
	 */
	public function buildUpdateResponse(
		PluginUpdateChannel $channel,
		string $installed_version,
		string $current_wp_version,
		string $current_php_version
	): ?array {
		// manifestのバージョンがインストール済み以下の場合は更新なしと判定し、
		// コアが`no_update`に登録できる有効なペイロードを返す。
		// これによりプラグイン一覧の`update-supported`がtrueとなり「自動更新を有効化」リンクが常時表示される。
		// ※ リンク表示（update-supported）は`response`/`no_update`への登録有無で決まり、`package`には依存しない。
		// `package`は将来`response`に入った際の実ダウンロードに必要なため含めている。
		// ※ semverとして解釈不能なバージョン文字列が来た場合はComparatorが例外を投げるため、
		// 呼び出し側（フック）で\Throwableを捕捉して更新非提示に倒す
		if ( ! Comparator::greaterThan( $channel->version(), $installed_version ) ) {
			// 更新なしパスでは要件チェックを行わないため、環境要件未達でも`requires`/`requires_php`が
			// そのまま載る。ただし`no_update`はコアがプラグイン一覧に表示しないため実害はない。
			return array(
				// コアは`version`フィールドが必須（無いと更新情報として無視される）
				'version'      => $installed_version,
				// インストール済み以下にすることでコアの`version_compare`がfalseとなり`no_update`に登録される
				'new_version'  => $installed_version,
				// 「詳細を見る」のリンク先。`slug`は意図的に含めない（含めると詳細モーダルがwordpress.orgへ遷移するため）
				'url'          => Config::UPDATE_URI,
				// 更新用zipのURL。将来`response`に入った際の実ダウンロードに必要
				'package'      => $channel->url(),
				'requires'     => $channel->requiresWordPress(),
				'requires_php' => $channel->requiresPhp(),
			);
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
