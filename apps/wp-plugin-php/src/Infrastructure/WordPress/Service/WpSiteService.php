<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Domain\Repository\InstallOriginUrl;
use Cornix\Serendipity\Core\Domain\Service\SiteService;

class WpSiteService implements SiteService {

	private InstallOriginUrl $install_origin_url;
	private WordPressPropertyProvider $wordpress_property_provider;

	public function __construct( InstallOriginUrl $install_origin_url, WordPressPropertyProvider $wordpress_property_provider ) {
		$this->install_origin_url          = $install_origin_url;
		$this->wordpress_property_provider = $wordpress_property_provider;
	}

	/** @inheritdoc */
	public function isInstallOriginUrlChanged(): bool {
		$install_origin_url = $this->install_origin_url->get();
		$current_home_url   = $this->wordpress_property_provider->homeUrl();

		return $this->normalize( $install_origin_url ) !== $this->normalize( $current_home_url );
	}

	private function normalize( string $url ): string {
		$trimmed = rtrim( strtolower( $url ), '/' );
		$trimmed = str_replace( '\\/', '/', $trimmed );

		return preg_replace( '/^https?:[\/\\\\]+/i', '', $trimmed ) ?? $trimmed;
	}
}
