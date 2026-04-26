<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Repository;

interface PurgeOnUninstallRepository {

	/** アンインストール時にデータを完全削除するかどうかを取得します */
	public function get(): bool;

	/** アンインストール時にデータを完全削除するかどうかを保存します */
	public function save( bool $purge_on_uninstall ): void;
}
