<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Domain\Repository\InstallOriginUrl;
use Baywall\Core\Domain\Service\SiteService;

class WpSiteService implements SiteService {


	public function __construct(
		private readonly InstallOriginUrl $install_origin_url,
		private readonly WordPressPropertyProvider $wordpress_property_provider
	) {}

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
