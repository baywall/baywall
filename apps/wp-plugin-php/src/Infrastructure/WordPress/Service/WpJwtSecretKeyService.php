<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtSecretKey;

/** WordPress用のJWT共通鍵サービス */
class WpJwtSecretKeyService {

	/** JWTの共通鍵を生成します */
	public function generate() {
		$secret = wp_generate_password( WpConfig::JWT_SECRET_KEY_LENGTH, false, false );
		return JwtSecretKey::from( $secret );
	}
}
