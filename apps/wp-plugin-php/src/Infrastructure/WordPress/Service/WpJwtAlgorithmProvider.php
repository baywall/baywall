<?php
declare(strict_types=1);
namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\JwtAlgorithmProvider;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Infrastructure\JWT\ValueObject\JwtAlgorithm;

class WpJwtAlgorithmProvider implements JwtAlgorithmProvider {
	/** @inheritDoc */
	public function get(): JwtAlgorithm {
		return JwtAlgorithm::from( WpConfig::JWT_ALGORITHM );
	}
}
