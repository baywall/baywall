<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Domain\Repository\RefreshTokenRepository;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\ValueObject\RefreshTokenString;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\ValueObject\WpRefreshTokenString;

class WpRefreshTokenService extends RefreshTokenService {

	public function __construct( RefreshTokenRepository $refresh_token_repository ) {
		parent::__construct( $refresh_token_repository );
	}

	/** @inheritdoc */
	protected function generateRefreshTokenString(): RefreshTokenString {
		return WpRefreshTokenString::generate();
	}

	/** @inheritdoc */
	protected function getExpiresAt(): UnixTimestamp {
		return UnixTimestamp::from( time() + WpConfig::REFRESH_TOKEN_EXPIRATION_DURATION );
	}
}
