<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\ValueObject;

use Baywall\Core\Application\ValueObject\PluginUpdateChannel;

/**
 * app-manifest.json の内容を表す値オブジェクト。
 *
 * JSON文字列を受け取り、デコード・構造検証を行ってデコード済みのデータを保持します。
 */
readonly class AppManifestData {
	private readonly string $buyer_legal_docs_url;
	private readonly string $seller_legal_docs_url;
	private readonly string $minimum_supported_version;

	/** @var array<string, PluginUpdateChannel> チャンネル名→チャンネルのマップ */
	private readonly array $channels;

	/**
	 * @param string $manifest_json app-manifest.jsonのJSON文字列
	 * @throws \RuntimeException JSONのパースまたは構造検証に失敗した場合
	 */
	public function __construct( string $manifest_json ) {
		try {
			$data = json_decode( $manifest_json, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			throw new \RuntimeException( '[466A8A3E] app-manifest.json returned invalid JSON.', 0, $e );
		}

		$word_press_plugin = is_array( $data ) ? ( $data['wordPressPlugin'] ?? null ) : null;
		$channels_raw      = is_array( $word_press_plugin ) ? ( $word_press_plugin['channels'] ?? null ) : null;
		// `channels` 本体が非配列の場合はエントリなしとみなし、チャンネル抽出時（呼び出し側）に失敗させる
		if ( ! is_array( $channels_raw ) ) {
			$channels_raw = array();
		}

		$channels = array();
		foreach ( $channels_raw as $channel_name => $channel ) {
			if ( ! is_array( $channel ) ) {
				throw new \RuntimeException( "[C0E6B9D0] app-manifest.json channel '{$channel_name}' is missing or not an object." );
			}
			// 全フィールドが非空文字列であることを検証（sha256Urlはチェックサム検証の前提のため必須扱い）
			foreach ( array( 'version', 'requiresWordPress', 'requiresPhp', 'url', 'sha256Url' ) as $field_name ) {
				if ( ! isset( $channel[ $field_name ] ) || ! is_string( $channel[ $field_name ] ) ) {
					throw new \RuntimeException( "[C0E6B9D0] app-manifest.json channel field '{$field_name}' is missing or not a string." );
				}
				if ( '' === $channel[ $field_name ] ) {
					throw new \RuntimeException( "[2FC2747D] app-manifest.json channel field '{$field_name}' must be a non-empty string." );
				}
			}
			$channels[ $channel_name ] = new PluginUpdateChannel(
				$channel['version'],
				$channel['requiresWordPress'],
				$channel['requiresPhp'],
				$channel['url'],
				$channel['sha256Url']
			);
		}

		$this->buyer_legal_docs_url      = $this->stringValue( $data, 'buyerLegalDocsUrl' );
		$this->seller_legal_docs_url     = $this->stringValue( $data, 'sellerLegalDocsUrl' );
		$this->minimum_supported_version = is_array( $word_press_plugin ) ? $this->stringValue( $word_press_plugin, 'minimumSupportedVersion' ) : '';
		$this->channels                  = $channels;
	}

	/** 文字列でない値は空文字として扱います（未検証フィールドの格納用）。 */
	private function stringValue( mixed $data, string $key ): string {
		return is_array( $data ) && isset( $data[ $key ] ) && is_string( $data[ $key ] ) ? $data[ $key ] : '';
	}

	/** 買い手向け法的文書のURLを取得します。 */
	public function buyerLegalDocsUrl(): string {
		return $this->buyer_legal_docs_url;
	}

	/** 売り手向け法的文書のURLを取得します。 */
	public function sellerLegalDocsUrl(): string {
		return $this->seller_legal_docs_url;
	}

	/** サポートするWordPressプラグインの最低バージョンを取得します。 */
	public function minimumSupportedVersion(): string {
		return $this->minimum_supported_version;
	}

	/**
	 * チャンネル名→チャンネルのマップを取得します。
	 *
	 * @return array<string, PluginUpdateChannel>
	 */
	public function channels(): array {
		return $this->channels;
	}
}
