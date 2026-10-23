<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Plugin;

use Baywall\Core\Application\Service\SupportedVersionConstraintsProvider;
use Composer\Semver\VersionParser;

/**
 * 同梱JSON（`resources/wp-versions.json` / `resources/php-versions.json`）から
 * サポート対象バージョンのComposer制約文字列を読み込むプロバイダ。
 *
 * - 読み込みはメソッド呼び出しごと（遅延・非キャッシュ）とし、同梱JSONの異常
 *   （ファイル不在・破損・制約文字列のパース不能）は fail-closed として `\RuntimeException` を送出します。
 * - 本クラスはWordPress環境に依存しないため、PHPUnitの標準`TestCase`で直接テスト可能です。
 */
class WpSupportedVersionConstraintsProvider implements SupportedVersionConstraintsProvider {

	/**
	 * @param string $wp_versions_json_path  WordPressバージョンの対応定義JSONへのパス
	 * @param string $php_versions_json_path PHPバージョンの対応定義JSONへのパス
	 */
	public function __construct(
		private readonly string $wp_versions_json_path,
		private readonly string $php_versions_json_path,
	) {}

	/**
	 * @inheritdoc
	 * @throws \RuntimeException JSONファイルが存在しない・読み込み失敗・JSONとして不正・`supported` が空配列の場合
	 */
	public function cmsVersionConstraints(): array {
		return $this->loadConstraints( $this->wp_versions_json_path );
	}

	/**
	 * @inheritdoc
	 * @throws \RuntimeException JSONファイルが存在しない・読み込み失敗・JSONとして不正・`supported` が空配列の場合
	 */
	public function phpVersionConstraints(): array {
		return $this->loadConstraints( $this->php_versions_json_path );
	}

	/**
	 * 対応バージョン定義JSONから制約文字列の一覧を読み込みます。
	 *
	 * 各制約文字列はここで事前検証し、パース不能な制約は同梱データ異常として fail-closed とします
	 * （制約のパース失敗を解釈不能なバージョンの例外捕捉で握らないため）。
	 *
	 * @param string $json_path 対応バージョン定義JSONへのパス
	 * @return string[] 制約文字列の配列
	 * @throws \RuntimeException JSONファイルが存在しない・読み込み失敗・JSONとして不正・`supported` が空配列の場合
	 */
	private function loadConstraints( string $json_path ): array {
		if ( ! file_exists( $json_path ) ) {
			throw new \RuntimeException( "[2149349F] Version definition JSON file not found: {$json_path}" );
		}

		$json_content = file_get_contents( $json_path );
		if ( false === $json_content ) {
			throw new \RuntimeException( "[9D36439C] Failed to read version definition JSON file: {$json_path}" );
		}

		$data = json_decode( $json_content, true );
		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( "[FC0B30BD] Invalid JSON in version definition file (decode failed): {$json_path}" );
		}

		$supported = $data['supported'] ?? null;
		if ( ! is_array( $supported ) ) {
			throw new \RuntimeException( "[A172DA22] Invalid JSON structure in version definition file (\"supported\" array is missing or invalid): {$json_path}" );
		}

		$constraints = array();
		foreach ( $supported as $constraint ) {
			if ( ! is_string( $constraint ) ) {
				throw new \RuntimeException( "[CDF4D2B8] Invalid JSON structure in version definition file (\"supported\" must contain only constraint strings): {$json_path}" );
			}

			try {
				( new VersionParser() )->parseConstraints( $constraint );
			} catch ( \UnexpectedValueException $e ) {
				throw new \RuntimeException( "[F6F2D0B3] Invalid JSON structure in version definition file (unparseable constraint \"{$constraint}\"): {$json_path}" );
			}

			$constraints[] = $constraint;
		}

		if ( array() === $constraints ) {
			throw new \RuntimeException( "[EF9B2AB0] Version definition JSON file has no supported constraints: {$json_path}" );
		}

		return $constraints;
	}
}
