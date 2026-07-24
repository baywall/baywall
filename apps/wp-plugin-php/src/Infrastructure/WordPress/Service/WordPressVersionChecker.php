<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

/**
 * WordPressのバージョンが、wp2shell脆弱性の影響を受けるバージョンに該当しないかをチェックするクラス。
 *
 * ## 背景
 * wp2shellは、WordPress 6.9.0〜6.9.4 / 7.0.0〜7.0.1 に存在する重大なセキュリティ脆弱性です。
 * これらのバージョンでは外部からの任意のコード実行が可能となるため、本プラグインはこれらを拒否します。
 *
 * ## 設計方針
 * - コンストラクタ依存を持たず、チェッカーパターン(`ArchitectureChecker` / `PhpExtChecker`)に回帰します。
 *   WordPress環境に依存しないため、PHPUnitの標準`TestCase`で直接テスト可能です。
 * - バージョン文字列は`checkVersion()`の引数で受け取ります。
 *   呼び出し側がバージョン取得の責務を持つことで、本クラスはWordPress環境(`get_bloginfo`等)に依存せず、
 *   純粋にバージョンの比較のみを行います。
 * - `isVulnerableVersion()`は静的純粋関数として分離しています。
 *   WordPress環境非依存の境界値テストを容易にするためです。
 */
class WordPressVersionChecker {

	/**
	 * 拒否対象のWordPressバージョン範囲。
	 *
	 * 範囲は `min` 以上 `max` 以下(両端含む)を脆弱バージョンとして扱います。
	 * - 6.9.0 〜 6.9.4: wp2shell脆弱性
	 * - 7.0.0 〜 7.0.1: wp2shell脆弱性
	 */
	private const VULNERABLE_RANGES = array(
		array(
			'min' => '6.9.0',
			'max' => '6.9.4',
		),
		array(
			'min' => '7.0.0',
			'max' => '7.0.1',
		),
	);

	/**
	 * 指定されたWordPressバージョンが脆弱バージョン範囲に該当するかを判定します。
	 *
	 * WordPress環境に依存しない純粋関数として実装しています。
	 * テスト容易性のため、静的メソッドとして公開し、境界値テストを直接行えるようにしています。
	 *
	 * @param string $version 判定対象のWordPressバージョン文字列(例: "6.9.0")
	 * @return bool 脆弱バージョンに該当する場合 true
	 */
	public static function isVulnerableVersion( string $version ): bool {
		foreach ( self::VULNERABLE_RANGES as $range ) {
			if ( version_compare( $version, $range['min'], '>=' ) && version_compare( $version, $range['max'], '<=' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 指定されたWordPressバージョンが脆弱バージョンに該当する場合、例外を送出します。
	 *
	 * バージョン文字列は引数で受け取ります。
	 * 呼び出し側がバージョン取得の責務を持つことで、本クラスはWordPress環境に依存せず、
	 * WordPressに依存しないテストが可能になります。
	 *
	 * @param string $version チェック対象のWordPressバージョン文字列
	 * @throws \RuntimeException バージョンが脆弱範囲に含まれる場合
	 */
	public function checkVersion( string $version ): void {
		if ( self::isVulnerableVersion( $version ) ) {
			throw new \RuntimeException( "[83BDD792] This plugin does not support WordPress version {$version} due to a critical security vulnerability (wp2shell). Please update WordPress to a patched version." );
		}
	}
}
