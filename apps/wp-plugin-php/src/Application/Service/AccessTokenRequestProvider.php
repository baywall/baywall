<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

/**
 * リクエストからアクセストークンを取得します。
 */
interface AccessTokenRequestProvider {
	public function get(): ?string;
}
