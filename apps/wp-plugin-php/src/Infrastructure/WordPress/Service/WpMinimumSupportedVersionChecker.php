<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Service\EnsRpcUrlResolver;
use Baywall\Core\Application\ValueObject\PluginVersion;
use Baywall\Core\Domain\Service\MinimumSupportedVersionChecker;
use Baywall\Core\Domain\Service\PluginInfoProvider;
use Baywall\Core\Infrastructure\Web3\Service\AppManifestFetchService;
use Baywall\Core\Infrastructure\Web3\ValueObject\AppManifestData;

class WpMinimumSupportedVersionChecker implements MinimumSupportedVersionChecker {
	public function __construct(
		private readonly EnsRpcUrlResolver $ens_rpc_url_resolver,
		private readonly AppManifestFetchService $app_manifest_fetch_service,
		private readonly PluginInfoProvider $plugin_info_provider,
		private readonly AppLogService $app_log_service
	) {}

	/** @inheritdoc */
	public function isBelowMinimumSupportedVersion(): ?bool {
		try {
			$rpc_url = $this->ens_rpc_url_resolver->resolve();
			if ( null === $rpc_url ) {
				return null;
			}

			$manifest_json = $this->app_manifest_fetch_service->fetch( $rpc_url );
			$minimum       = ( new AppManifestData( $manifest_json ) )->minimumSupportedVersion();
			if ( '' === $minimum ) {
				return null;
			}

			$current         = PluginVersion::from( $this->plugin_info_provider->version() );
			$minimum_version = PluginVersion::from( $minimum );

			return $current->compareOperator( $minimum_version, '<' );
		} catch ( \Throwable $e ) {
			$this->app_log_service->warn( $e );
			return null;
		}
	}
}
