<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\AccessTokenExpirationProvider;
use Baywall\Core\Application\Service\Clock;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;

/** WordPress環境におけるアクセストークンの有効期限提供クラス */
class WpAccessTokenExpirationProvider implements AccessTokenExpirationProvider {

	public function __construct( private readonly Clock $clock ) {}

	public function get(): UnixTimestamp {
		return $this->clock->now()->addSeconds( WpConfig::ACCESS_TOKEN_EXPIRATION );
	}
}
