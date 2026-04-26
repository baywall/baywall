<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Repository;

use Cornix\Serendipity\Core\Application\Repository\PurgeOnUninstallRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\OptionGateway\Option\BoolOption;

/**
 * アンインストール時にデータを完全削除するかどうかを取得または保存するクラス
 */
class WpPurgeOnUninstallRepository implements PurgeOnUninstallRepository {

	private BoolOption $option;

	public function __construct() {
		$this->option = new BoolOption( WpOptionName::PURGE_ON_UNINSTALL );
	}

	/** 完全削除フラグを取得します */
	public function get(): bool {
		$purge_on_uninstall = $this->option->get();
		return $purge_on_uninstall ?? false;
	}

	/** 完全削除フラグを保存します */
	public function save( bool $purge_on_uninstall ): void {
		$this->option->update( $purge_on_uninstall );
	}

	/** 完全削除フラグを削除します */
	public function delete(): void {
		$this->option->delete();
	}
}
