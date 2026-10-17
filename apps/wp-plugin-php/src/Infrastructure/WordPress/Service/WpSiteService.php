<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Domain\Repository\InitialSiteUrlRepository;
use Baywall\Core\Domain\Service\SiteService;

class WpSiteService implements SiteService {


	public function __construct(
		private readonly InitialSiteUrlRepository $initial_site_url,
		private readonly WordPressPropertyProvider $wordpress_property_provider
	) {}

	/** @inheritdoc */
	public function hasSiteUrlChangedSinceInstall(): bool {
		$initial_site_url = $this->initial_site_url->get();
		$current_home_url = $this->wordpress_property_provider->homeUrl();

		return $this->normalize( $initial_site_url->value() ) !== $this->normalize( $current_home_url );
	}

	private function normalize( string $url ): string {
		$trimmed = rtrim( strtolower( $url ), '/' );
		$trimmed = str_replace( '\\/', '/', $trimmed );

		return preg_replace( '/^https?:[\/\\\\]+/i', '', $trimmed ) ?? $trimmed;
	}
}
