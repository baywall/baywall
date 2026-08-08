<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\Config;

/**
 * WordPress公式REST APIからWordPressコアの最新マイナーバージョン一覧を取得するサービス。
 *
 * `https://api.wordpress.org/core/version-check/1.7/` を呼び出し、`offers[].current` を抽出します。
 * API呼び出しの取得・検証に失敗した場合は `\RuntimeException` を送出します。
 * `getVersions()` は例外をキャッチし、コンストラクタで注入された
 * `LocalWordPressCoreLatestVersionService`(ローカルJSON)にフォールバックします。
 *
 * 空配列をそのまま返すと全バージョンが拒否されるため、必ずフォールバックします。
 *
 * Transientキャッシュは導入しません。
 * 本チェックはプラグインの初回インストール時のみ実行されるため、キャッシュは不要です。
 */
class WpWordPressCoreLatestVersionService implements WordPressCoreLatestVersionService {

	/** WordPressコアのバージョンチェックAPIのURL */
	private const API_URL = 'https://api.wordpress.org/core/version-check/1.7/';

	/** @var LocalWordPressCoreLatestVersionService フォールバック用のローカルJSON実装 */
	private LocalWordPressCoreLatestVersionService $fallback;

	public function __construct( LocalWordPressCoreLatestVersionService $fallback ) {
		$this->fallback = $fallback;
	}

	/**
	 * @inheritdoc
	 *
	 * REST APIからの取得・検証に失敗した場合は例外をキャッチし、ローカルJSONにフォールバックします。
	 * 予期せぬ例外(`\Throwable`)も含めキャッチしてフォールバックします。
	 */
	public function getVersions(): array {
		try {
			return $this->fetchFromApi();
		} catch ( \Throwable $e ) {
			// REST APIからの取得・検証で予期せぬ例外が発生した場合も含め、ローカルJSONにフォールバックする
			return $this->fallback->getVersions();
		}
	}

	/**
	 * REST APIから最新マイナーバージョン一覧を取得します。
	 *
	 * 取得・検証に失敗した場合は `\RuntimeException` を送出します。
	 * (呼び出し側でキャッチし、フォールバックの判定に使用します)
	 *
	 * @return string[] 取得成功時はバージョン一覧
	 * @throws \RuntimeException 取得・検証に失敗した場合
	 */
	private function fetchFromApi(): array {
		$response = wp_remote_get(
			self::API_URL,
			array(
				'timeout' => Config::WP_CORE_VERSION_REQUEST_TIMEOUT,
			)
		);

		// WP_Errorチェック
		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( '[F583A4E5] WordPress core version check API request failed: ' . $response->get_error_message() );
		}

		// HTTPステータスコード200確認
		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			throw new \RuntimeException( "[DBB79447] WordPress core version check API returned HTTP status {$status_code}." );
		}

		// JSONデコード
		$body = wp_remote_retrieve_body( $response );
		try {
			$data = json_decode( $body, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			throw new \RuntimeException( '[D67D1929] WordPress core version check API returned invalid JSON.', 0, $e );
		}

		// offers配列の存在・型検証
		if ( ! is_array( $data ) || ! isset( $data['offers'] ) || ! is_array( $data['offers'] ) ) {
			throw new \RuntimeException( '[C45482D1] WordPress core version check API response missing valid offers array.' );
		}

		// 各 offers[].current を VersionParser::normalize() でバリデーションして抽出
		$versions = LocalWordPressCoreLatestVersionService::extractVersions( $data['offers'] );

		// 抽出結果が空配列の場合は異常とみなす
		if ( array() === $versions ) {
			throw new \RuntimeException( '[05CC00C4] WordPress core version check API returned no valid versions.' );
		}

		return $versions;
	}
}
