<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\ValueObject\PluginUpdateChannel;
use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Service\EnsService;

/**
 * app-manifest.jsonを取得し、WordPressプラグインのリリースチャンネル情報を返すサービス。
 *
 * `Config::APP_MANIFEST_ENS` の `Config::ENS_TEXT_FIELD_NAME_MANIFEST` テキストレコードから
 * manifestのURLを解決し、取得・JSONパース・構造検証して
 * `wordPressPlugin.channels.{チャンネル}` エントリを `PluginUpdateChannel` として返します。
 *
 * キャッシュ機構は導入せず、チェックのたびに都度取得します。
 * 取得・検証に失敗した場合は `\RuntimeException` を送出します（呼び出し側で「更新非提示」として扱います）。
 */
class WpAppManifestFetcher {
	public function __construct( private readonly EnsService $ens_service ) {}

	/**
	 * app-manifest.jsonを取得し、設定されたチャンネルのリリースチャンネル情報を返します。
	 *
	 * @param RpcUrl $rpc_url ENS解決に使用するRPCエンドポイント
	 * @throws \RuntimeException manifestの取得・検証に失敗した場合
	 */
	public function fetch( RpcUrl $rpc_url ): PluginUpdateChannel {
		$manifest_url = $this->ens_service->getText( $rpc_url, Config::APP_MANIFEST_ENS, Config::ENS_TEXT_FIELD_NAME_MANIFEST );
		if ( '' === $manifest_url ) {
			// URL未設定は取得失敗として扱う（呼び出し側のフェイルセーフに倒す）
			throw new \RuntimeException( '[842E73E7] manifest URL is not set in the ENS text record.' );
		}
		// 更新zipは任意コード実行に直結するため、平文取得を防ぐ目的でhttpsスキームのみ受け付ける
		// （`Url::checkValidUrlFormat`はhttp始まりしか検査しないため使用しない）
		$scheme = wp_parse_url( $manifest_url, PHP_URL_SCHEME );
		if ( ! is_string( $scheme ) || 'https' !== strtolower( $scheme ) ) {
			throw new \RuntimeException( "[8812B2B0] manifest URL must use the https scheme. - {$manifest_url}" );
		}

		$response = wp_remote_get(
			$manifest_url,
			array(
				'timeout' => Config::APP_MANIFEST_REQUEST_TIMEOUT,
			)
		);

		// WP_Errorチェック
		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( '[A42D263C] app-manifest.json request failed: ' . $response->get_error_message() );
		}

		// HTTPステータスコード200確認
		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			throw new \RuntimeException( "[91E6B934] app-manifest.json returned HTTP status {$status_code}." );
		}

		// JSONデコード + 構造検証
		$body = wp_remote_retrieve_body( $response );
		return self::parseChannel( $body, Config::PLUGIN_UPDATE_CHANNEL_NAME );
	}

	/**
	 * manifestのJSON文字列から指定チャンネルのリリースチャンネル情報を生成します。
	 *
	 * WordPressに依存しない純粋なパース処理のため静的メソッドとして分離しています。
	 *
	 * @param string $manifest_json manifestのJSON文字列
	 * @param string $channel_name  取得するリリースチャンネル名
	 * @throws \RuntimeException JSONのパースまたは構造検証に失敗した場合
	 */
	public static function parseChannel( string $manifest_json, string $channel_name ): PluginUpdateChannel {
		try {
			$data = json_decode( $manifest_json, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			throw new \RuntimeException( '[466A8A3E] app-manifest.json returned invalid JSON.', 0, $e );
		}

		// channels.{チャンネル} の存在・型検証
		if ( ! is_array( $data )
			|| ! isset( $data['wordPressPlugin']['channels'][ $channel_name ] )
			|| ! is_array( $data['wordPressPlugin']['channels'][ $channel_name ] )
		) {
			throw new \RuntimeException( "[56F01B11] app-manifest.json is missing the '{$channel_name}' channel entry." );
		}

		$channel = $data['wordPressPlugin']['channels'][ $channel_name ];

		// 全フィールドが非空文字列であることを検証（sha256Urlはチェックサム検証の前提のため必須扱い）
		foreach ( array( 'version', 'requiresWordPress', 'requiresPhp', 'url', 'sha256Url' ) as $field_name ) {
			if ( ! isset( $channel[ $field_name ] ) || ! is_string( $channel[ $field_name ] ) ) {
				throw new \RuntimeException( "[C0E6B9D0] app-manifest.json channel field '{$field_name}' is missing or not a string." );
			}
			if ( '' === $channel[ $field_name ] ) {
				throw new \RuntimeException( "[2FC2747D] app-manifest.json channel field '{$field_name}' must be a non-empty string." );
			}
		}

		return new PluginUpdateChannel(
			$channel['version'],
			$channel['requiresWordPress'],
			$channel['requiresPhp'],
			$channel['url'],
			$channel['sha256Url']
		);
	}
}
