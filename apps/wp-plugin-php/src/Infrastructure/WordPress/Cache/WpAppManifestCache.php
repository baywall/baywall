<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Cache;

use Baywall\Core\Infrastructure\Cache\AppManifestCache;
use Baywall\Core\Infrastructure\WordPress\Constants\WpTransientName;

class WpAppManifestCache implements AppManifestCache {
	public function __construct( TransientStorage $storage, TransientExpirationProvider $expiration_provider ) {
		$this->storage    = $storage;
		$this->expiration = $expiration_provider->appManifest();
	}

	private readonly TransientStorage $storage;
	private readonly int $expiration;

	public function set( string $manifest_json ): void {
		$this->storage->set( WpTransientName::APP_MANIFEST, $manifest_json, $this->expiration );
	}

	public function get(): ?string {
		$result = $this->storage->get( WpTransientName::APP_MANIFEST );
		return is_string( $result ) ? $result : null;
	}
}
