<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\ValueObject\Url;

/** 初回インストール時のURLを取得するクラス */
interface InitialSiteUrlRepository {

	/** 初回インストール時のURLを取得します */
	public function get(): Url;
}
