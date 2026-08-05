<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\Config;

/**
 * プラグイン更新用zipパッケージのsha256チェックサムを検証するクラス。
 *
 * `upgrader_pre_download`フィルターにおいてコアのダウンロードを肩代わりし、
 * 展開前にsha256チェックサムを検証した上でローカルパスを返し、コアの展開処理に引き継ぐ。
 *
 * Fail-closed: 検証を継続できない状況（manifest取得失敗・URL不一致・チェックサム不一致など）では
 * `WP_Error`を返して更新自体を中止させる。
 */
class WpPluginPackageChecksumVerifier {

	public function __construct(
		WpAppManifestFetcher $manifest_fetcher,
		WpPluginInfoProvider $plugin_info_provider
	) {
		$this->manifest_fetcher     = $manifest_fetcher;
		$this->plugin_info_provider = $plugin_info_provider;
	}
	private WpAppManifestFetcher $manifest_fetcher;
	private WpPluginInfoProvider $plugin_info_provider;

	/**
	 * `upgrader_pre_download`フィルターでの事前検証を行います。
	 *
	 * @param string       $package    パッケージのURL
	 * @param \WP_Upgrader $upgrader   アップグレーダーのインスタンス（本検証では未使用）
	 * @param array        $hook_extra フィルタに渡された追加引数（type/plugin等）
	 * @return false|string|\WP_Error 対象外はfalse、検証成功時は一時ファイルパス、検証失敗時はWP_Error
	 */
	public function verifyPreDownload( string $package, \WP_Upgrader $upgrader, array $hook_extra ) {
		// 対象外は素通し（他プラグイン・コア・テーマの更新に影響しない）
		if ( ! isset( $hook_extra['type'] ) || 'plugin' !== $hook_extra['type'] ) {
			return false;
		}
		if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->pluginBaseName() ) {
			return false;
		}

		// manifestをこの時点で再取得（fail-closed: 取得失敗時は更新自体を中止する）
		try {
			$channel = $this->manifest_fetcher->fetch();
		} catch ( \Throwable $e ) {
			return new \WP_Error( 'baywall_checksum_verification_failed', '[A8FA65AE] Failed to fetch app-manifest.json: ' . $e->getMessage() );
		}

		// URL整合確認
		if ( $package !== $channel->url() ) {
			return new \WP_Error( 'baywall_checksum_verification_failed', "[4EFC8592] Package URL ({$package}) does not match app-manifest.json URL ({$channel->url()})." );
		}

		// sha256チェックサムファイルを取得
		$expected_sha256 = $this->fetchExpectedSha256( $channel->sha256Url() );
		if ( is_wp_error( $expected_sha256 ) ) {
			return $expected_sha256;
		}

		// 更新用zipを一時ファイルへダウンロード（コアのダウンロードを肩代わり）
		// `download_url`はwp-admin/includes/file.phpに定義されるが、cron(自動更新)経路では未読込の可能性があるため防御的にrequireする
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$tmp_path = download_url( $package, Config::PLUGIN_UPDATE_PACKAGE_DOWNLOAD_TIMEOUT );
		if ( is_wp_error( $tmp_path ) ) {
			return new \WP_Error( 'baywall_package_download_failed', '[14CFFD45] Failed to download the update package. ' . $tmp_path->get_error_message(), $tmp_path->get_error_data() );
		}

		// ダウンロードしたzipのsha256ハッシュを算出して照合（hash_equalsでタイミング攻撃対策）
		$actual_sha256 = hash_file( 'sha256', $tmp_path );
		if ( false === $actual_sha256 ) {
			unlink( $tmp_path );
			return new \WP_Error( 'baywall_checksum_mismatch', "[9C9CA25F] Failed to calculate sha256 hash of the downloaded package. ({$tmp_path})" );
		}
		if ( ! hash_equals( strtolower( $expected_sha256 ), strtolower( $actual_sha256 ) ) ) {
			unlink( $tmp_path );
			return new \WP_Error( 'baywall_checksum_mismatch', "[7BDBBEA2] Checksum verification failed. expected: {$expected_sha256}, actual: {$actual_sha256}" );
		}

		// 検証済みの一時ファイルパスを返す。コアはこれをローカルパッケージとして展開し、展開後に自動削除する
		return $tmp_path;
	}

	/**
	 * プラグインのベース名を取得します。
	 */
	private function pluginBaseName(): string {
		return plugin_basename( $this->plugin_info_provider->mainFilePath() );
	}

	/**
	 * sha256チェックサムファイルを取得・パースします。
	 *
	 * CDは`sha256sum "$ZIP" > "$ZIP.sha256"`形式（「<hex><空白類><ファイル名>」）を生成するため、
	 * 先頭トークンのみを採用します。
	 *
	 * @param string $sha256_url チェックサムファイルのURL
	 * @return string|\WP_Error 成功時は期待チェックサム(16進数64桁の文字列)、失敗時はWP_Error
	 */
	private function fetchExpectedSha256( string $sha256_url ) {
		$response = wp_remote_get(
			$sha256_url,
			array(
				'timeout' => Config::APP_MANIFEST_REQUEST_TIMEOUT,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'baywall_checksum_verification_failed', '[89EA2739] Failed to fetch the sha256 checksum file: ' . $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new \WP_Error( 'baywall_checksum_verification_failed', "[27F45E35] The sha256 checksum file returned HTTP status {$status_code}." );
		}

		// 先頭トークンがsha256の16進数であることを確認
		$body      = trim( wp_remote_retrieve_body( $response ) );
		$tokens    = preg_split( '/\s+/', $body );
		$candidate = ( is_array( $tokens ) && isset( $tokens[0] ) ) ? $tokens[0] : '';
		if ( 1 !== preg_match( '/^[a-f0-9]{64}$/i', $candidate ) ) {
			return new \WP_Error( 'baywall_checksum_verification_failed', '[3A5D6F26] The sha256 checksum file body is not a valid sha256 hash.' );
		}

		return $candidate;
	}
}
