<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\AccessTokenExpirationProvider;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;

/** WordPress環境におけるアクセストークンの有効期限提供クラス */
class WpAccessTokenExpirationProvider implements AccessTokenExpirationProvider {

	public function get(): UnixTimestamp {
		return UnixTimestamp::from( UnixTimestamp::now()->value() + WpConfig::ACCESS_TOKEN_EXPIRATION );
	}
}
