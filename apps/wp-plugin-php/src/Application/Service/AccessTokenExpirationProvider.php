<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\ValueObject\UnixTimestamp;

/** アクセストークンの有効期限を提供するクラス */
interface AccessTokenExpirationProvider {
	/** アクセストークンの有効期限を取得します */
	public function get(): UnixTimestamp;
}
