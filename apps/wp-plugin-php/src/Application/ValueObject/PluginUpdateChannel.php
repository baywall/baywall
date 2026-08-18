<?php
declare(strict_types=1);

namespace Baywall\Core\Application\ValueObject;

/**
 * app-manifest.json の `wordPressPlugin.channels.{channel}` エントリを表す値オブジェクト。
 *
 * バージョン情報とダウンロード用URL、および中間者改ざん対策用のsha256ファイルURLを保持する。
 * いずれのフィールドも非空文字列であることが前提であり、空文字・型違いの場合は`\InvalidArgumentException`を送出する。
 */
class PluginUpdateChannel {

	public function __construct(
		string $version,
		string $requires_wordpress,
		string $requires_php,
		string $url,
		string $sha256_url
	) {
		$this->assertNonEmptyString( $version, 'version', '[7C8A91DE]' );
		$this->assertNonEmptyString( $requires_wordpress, 'requiresWordPress', '[0181D7C5]' );
		$this->assertNonEmptyString( $requires_php, 'requiresPhp', '[B2B0E196]' );
		$this->assertNonEmptyString( $url, 'url', '[6273710F]' );
		$this->assertNonEmptyString( $sha256_url, 'sha256Url', '[D8E64E7C]' );

		$this->version            = $version;
		$this->requires_wordpress = $requires_wordpress;
		$this->requires_php       = $requires_php;
		$this->url                = $url;
		$this->sha256_url         = $sha256_url;
	}

	private string $version;
	private string $requires_wordpress;
	private string $requires_php;
	private string $url;
	private string $sha256_url;

	/** 更新先のプラグインバージョンを取得します。 */
	public function version(): string {
		return $this->version;
	}

	/** 更新先プラグインが要求するWordPressの最低バージョンを取得します。 */
	public function requiresWordPress(): string {
		return $this->requires_wordpress;
	}

	/** 更新先プラグインが要求するPHPの最低バージョンを取得します。 */
	public function requiresPhp(): string {
		return $this->requires_php;
	}

	/** 更新用zipファイルのダウンロードURLを取得します。 */
	public function url(): string {
		return $this->url;
	}

	/** 更新用zipファイルのsha256チェックサムファイルのURLを取得します。 */
	public function sha256Url(): string {
		return $this->sha256_url;
	}

	/**
	 * フィールドが非空文字列であることを検証します。
	 *
	 * @throws \InvalidArgumentException 空文字の場合
	 */
	private function assertNonEmptyString( string $value, string $field_name, string $id ): void {
		if ( '' === $value ) {
			throw new \InvalidArgumentException( "[{$id}] Plugin update channel field '{$field_name}' must be a non-empty string." );
		}
	}
}
