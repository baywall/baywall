<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

/**
 * リクエストからアクセストークンを取得します。
 */
interface AccessTokenRequestProvider {
	public function get(): ?string;
}
