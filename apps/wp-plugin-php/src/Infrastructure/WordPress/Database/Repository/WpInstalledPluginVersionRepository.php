<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Application\ValueObject\PluginVersion;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\OptionGateway\Option\StringOption;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;

/**
 * プラグインバージョンを取得または保存するクラス
 * プラグインがインストールされた時にこの値が書き込まれます。
 *
 * プラグインのマイグレーションが必要かどうかを、
 * この値と現在のプラグインバージョンを比較して判断するために使用します。
 */
class WpInstalledPluginVersionRepository {

	private StringOption $option;

	public function __construct() {
		$this->option = new StringOption( WpOptionName::PLUGIN_VERSION );
	}

	/** インストール済みのプラグインバージョンを取得します */
	public function get(): ?PluginVersion {
		$version_str = $this->option->get();
		return $version_str !== null ? PluginVersion::from( $version_str ) : null;
	}

	/** インストール済みのプラグインバージョンを更新します */
	public function update( PluginVersion $version ): void {
		// 管理画面で使用するだけなので autoload は false で保存
		$this->option->update( $version->value(), false );
	}
}
