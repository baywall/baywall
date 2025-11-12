<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\JwtSecretKeyProvider;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\ValueObject\JwtSecretKey;

class WpJwtSecretKeyProvider implements JwtSecretKeyProvider {

	private WordPressOptionService $option_service;
	private string $option_name;

	public function __construct( WordPressOptionService $option_service, OptionNameProvider $option_name_provider ) {
		$this->option_service = $option_service;
		$this->option_name    = $option_name_provider->jwtSecretKey();
	}

	public function get(): JwtSecretKey {
		/** @var string|null */
		$secret = $this->option_service->get( $this->option_name, null );

		if ( $secret === null ) {
			$secret = wp_generate_password( WpConfig::JWT_SECRET_KEY_LENGTH, false, false );
			$this->option_service->update( $this->option_name, $secret, true ); // autoload = true
		}

		return JwtSecretKey::from( $secret );
	}
}
