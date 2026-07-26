<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Composer\Semver\VersionParser;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpConfig;

/**
 * ローカルJSONファイルからWordPressコアの最新マイナーバージョン一覧を取得するサービス。
 *
 * `WpConfig::WP_CORE_VERSION_CHECK_JSON_PATH` で定義されたパスのJSONファイルを読み込み、
 * `offers[].current` フィールドを抽出して最新マイナーバージョン一覧を返します。
 *
 * バージョン形式のバリデーションには `composer/semver` の `VersionParser::normalize()` を使用し、
 * 不正なバージョン形式のエントリは除外します。
 *
 * 本クラスはWordPress環境に依存しないため、PHPUnitの標準`TestCase`で直接テスト可能です。
 */
class LocalWordPressCoreLatestVersionService implements WordPressCoreLatestVersionService {

	/**
	 * @inheritdoc
	 * @throws \RuntimeException JSONファイルの読み込みに失敗した場合、または抽出結果が空配列の場合
	 */
	public function getVersions(): array {
		$json_path = WpConfig::WP_CORE_VERSION_CHECK_JSON_PATH;

		if ( ! file_exists( $json_path ) ) {
			throw new \RuntimeException( "[BE0AE1D7] WordPress core version check JSON file not found: {$json_path}" );
		}

		$json_content = file_get_contents( $json_path );
		if ( false === $json_content ) {
			throw new \RuntimeException( "[50404032] Failed to read WordPress core version check JSON file: {$json_path}" );
		}

		$data = json_decode( $json_content, true );
		if ( ! is_array( $data ) || ! isset( $data['offers'] ) || ! is_array( $data['offers'] ) ) {
			throw new \RuntimeException( '[6BC2A92D] Invalid JSON structure in WordPress core version check file: "offers" array is missing or invalid.' );
		}

		$versions = self::extractVersions( $data['offers'] );

		if ( array() === $versions ) {
			throw new \RuntimeException( '[231CCC8E] No valid WordPress core versions found in the version check JSON file.' );
		}

		return $versions;
	}

	/**
	 * offers配列から `current` フィールドを抽出し、重複除去したバージョン一覧を返します。
	 *
	 * バージョン形式のバリデーションに `VersionParser::normalize()` を使用し、
	 * `\UnexpectedValueException` が送出されたエントリは不正なバージョン形式として除外します。
	 *
	 * @param array $offers JSONの `offers` 配列
	 * @return string[] 重複除去されたバージョン文字列の配列
	 */
	public static function extractVersions( array $offers ): array {
		$version_parser = new VersionParser();
		$versions       = array();

		foreach ( $offers as $offer ) {
			if ( ! is_array( $offer ) || ! isset( $offer['current'] ) || ! is_string( $offer['current'] ) ) {
				continue;
			}

			$current = $offer['current'];

			// VersionParser::normalize() でバージョン形式を検証し、不正な形式は除外する
			try {
				$version_parser->normalize( $current );
			} catch ( \UnexpectedValueException $e ) {
				continue;
			}

			$versions[ $current ] = true;
		}

		return array_keys( $versions );
	}
}
