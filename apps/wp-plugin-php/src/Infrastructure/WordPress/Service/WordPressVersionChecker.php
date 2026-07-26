<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;

/**
 * WordPressのバージョンが、最新のマイナーバージョン一覧を基準にサポート対象かどうかを判定するクラス。
 *
 * ## 背景
 * WordPress公式API(`https://api.wordpress.org/core/version-check/1.7/`)が提供する
 * 最新マイナーバージョン一覧を基準に、現在のWordPressバージョンが最新パッチ適用済みかどうかを判定します。
 * 旧方式(脆弱バージョンのハードコード範囲指定)と異なり、新しい脆弱性パッチがリリースされても
 * プラグイン側の定数更新が不要です。
 *
 * ## 設計方針
 * - コンストラクタDIで `WordPressCoreLatestVersionService` を注入します。
 *   本番環境ではREST API実装(`WpWordPressCoreLatestVersionService`)、
 *   テスト環境ではローカルJSON実装(`LocalWordPressCoreLatestVersionService`)が注入されます。
 * - バージョン文字列は`checkVersion()`の引数で受け取ります。
 *   呼び出し側がバージョン取得の責務を持つことで、本クラスはWordPress環境(`get_bloginfo`等)に依存しません。
 * - 判定ロジック:
 *   1. サーバーのWPバージョンと一覧の各バージョンを `VersionParser::normalize()` で正規化
 *   2. 正規化後の上位2要素(`major.minor`)が一致するエントリを一覧から抽出
 *   3. 一致するエントリがある場合: `Comparator::greaterThanOrEqualTo()` で判定
 *      - サーバーのWPバージョンが最新バージョン以上 → 許可
 *      - 古い場合 → `[F07B624C]` の `\RuntimeException` を送出
 *   4. 一致するマイナーバージョンが一覧に存在しない場合:
 *      一覧より新しいマイナーブランチとみなし、許可する(false positive防止)
 */
class WordPressVersionChecker {

	/** @var WordPressCoreLatestVersionService 最新マイナーバージョン一覧の取得サービス */
	private WordPressCoreLatestVersionService $version_service;

	public function __construct( WordPressCoreLatestVersionService $version_service ) {
		$this->version_service = $version_service;
	}

	/**
	 * 指定されたWordPressバージョンが最新パッチ適用済みかどうかを判定し、
	 * 未適用の場合は例外を送出します。
	 *
	 * @param string $version チェック対象のWordPressバージョン文字列(例: "6.9.3")
	 * @throws \RuntimeException 最新パッチ未適用の場合([F07B624C])
	 */
	public function checkVersion( string $version ): void {
		$latest_versions = $this->version_service->getVersions();

		// サーバーバージョンが正規化不能な形式(開発版ビルド `6.9.3-src-59000` 等)の場合、
		// `VersionParser::normalize()` が `\UnexpectedValueException` を送出する。
		// 旧方式(`version_compare()`)では `-src-` 形式でも比較が成立していたため、
		// 判定不能とみなして許可し、開発者/nightly 環境での false positive(誤検知)を防止する。
		try {
			$latest_minor_version = $this->findLatestMinorVersion( $version, $latest_versions );
		} catch ( \UnexpectedValueException $e ) {
			return;
		}

		// 一致するマイナーバージョンが一覧に存在しない場合:
		// 一覧より新しいマイナーブランチとみなし、許可する(false positive防止)
		if ( null === $latest_minor_version ) {
			return;
		}

		// サーバーのWPバージョンが最新バージョン以上なら許可、古い場合は例外
		if ( ! Comparator::greaterThanOrEqualTo( $version, $latest_minor_version ) ) {
			throw new \RuntimeException( "[F07B624C] This plugin does not support WordPress version {$version}. The latest patch version for this minor branch is {$latest_minor_version}. Please update WordPress to the latest version." );
		}
	}

	/**
	 * 一覧から、指定バージョンのマイナーバージョン(`major.minor`)に一致する最新バージョンを返します。
	 *
	 * `VersionParser::normalize()` で各バージョンを正規化し、
	 * 正規化後の上位2要素(`major.minor`)を比較します。
	 * 同一マイナーブランチに複数のエントリがある場合は、最も新しいバージョンを返します。
	 *
	 * @param string   $version         比較対象のWordPressバージョン文字列
	 * @param string[] $latest_versions 最新マイナーバージョン一覧
	 * @return string|null 一致する最新バージョン。一致しない場合は null
	 */
	private function findLatestMinorVersion( string $version, array $latest_versions ): ?string {
		$version_parser    = new VersionParser();
		$normalized_server = $version_parser->normalize( $version );
		$server_minor      = $this->extractMajorMinor( $normalized_server );

		$matched_version = null;

		foreach ( $latest_versions as $candidate ) {
			$normalized_candidate = $version_parser->normalize( $candidate );
			$candidate_minor      = $this->extractMajorMinor( $normalized_candidate );

			if ( $server_minor !== $candidate_minor ) {
				continue;
			}

			// 同一マイナーブランチに複数エントリがある場合は最も新しいバージョンを採用
			if ( null === $matched_version || Comparator::greaterThan( $candidate, $matched_version ) ) {
				$matched_version = $candidate;
			}
		}

		return $matched_version;
	}

	/**
	 * 正規化済みバージョン文字列から `major.minor` を抽出します。
	 *
	 * 例: `'6.9.5.0'` → `'6.9'`
	 *
	 * @param string $normalized_version `VersionParser::normalize()` で正規化済みのバージョン文字列
	 * @return string `major.minor` 形式の文字列
	 */
	private function extractMajorMinor( string $normalized_version ): string {
		$parts = explode( '.', $normalized_version );
		return $parts[0] . '.' . $parts[1];
	}
}
