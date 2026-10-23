<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Plugin;

class WpPluginAssetUrlProvider {

	public function __construct( private readonly WpPluginInfoProvider $plugin_info_provider ) {}

	/** 指定された相対パスをURLに変換します。 */
	public function url( string $relative_path ): string {
		assert( strpos( $relative_path, '/' ) !== 0, '[9172DD54] The relative path must not be an absolute path.' );
		assert( strpos( $relative_path, '..' ) === false, '[6CB69F1F] The relative path must not contain "..".' );

		// `plugin_dir_url`の戻り値は最後にスラッシュが付いている
		return plugin_dir_url( $this->plugin_info_provider->mainFilePath() ) . $relative_path;
	}
}
