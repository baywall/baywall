<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

/** インストール元URLを取得または保存するクラス */
interface InstallOriginUrl {

	/** インストール元URLを取得します */
	public function get(): string;

	/** インストール元URLを保存します */
	public function save( string $url ): void;
}
