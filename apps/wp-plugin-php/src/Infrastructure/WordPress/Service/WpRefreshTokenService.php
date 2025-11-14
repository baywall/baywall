<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\Repository\RefreshTokenRepository;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\RefreshTokenString;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\WpRefreshTokenString;

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
