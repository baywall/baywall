<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\AccessTokenExpirationProvider;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

/** WordPress環境におけるアクセストークンの有効期限提供クラス */
class WpAccessTokenExpirationProvider implements AccessTokenExpirationProvider {

	public function get(): UnixTimestamp {
		return UnixTimestamp::from( UnixTimestamp::now()->value() + WpConfig::ACCESS_TOKEN_EXPIRATION );
	}
}
