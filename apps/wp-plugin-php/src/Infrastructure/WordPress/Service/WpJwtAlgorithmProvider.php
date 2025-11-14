<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\JwtAlgorithmProvider;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtAlgorithm;

class WpJwtAlgorithmProvider implements JwtAlgorithmProvider {
	/** @inheritDoc */
	public function get(): JwtAlgorithm {
		return JwtAlgorithm::from( WpConfig::JWT_ALGORITHM );
	}
}
