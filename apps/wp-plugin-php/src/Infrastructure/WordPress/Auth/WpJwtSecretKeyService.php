<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Auth;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Application\ValueObject\JwtSecretKey;

/** WordPress用のJWT共通鍵サービス */
class WpJwtSecretKeyService {

	/** JWTの共通鍵を生成します */
	public function generate() {
		$secret = wp_generate_password( WpConfig::JWT_SECRET_KEY_LENGTH, false, false );
		return JwtSecretKey::from( $secret );
	}
}
