<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Domain\Repository\RefreshTokenRepository;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\RefreshTokenString;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\WpRefreshTokenString;

class WpRefreshTokenService extends RefreshTokenService {

	public function __construct( RefreshTokenRepository $refresh_token_repository ) {
		parent::__construct( $refresh_token_repository );
	}

	protected function generateRefreshTokenString(): RefreshTokenString {
		return WpRefreshTokenString::generate();
	}
}
