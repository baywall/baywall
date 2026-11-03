<?php
declare(strict_types=1);
namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Port\CrawlFailureNotificationMessageProvider;
use Baywall\Core\Domain\ValueObject\ChainId;

class WpCrawlFailureNotificationMessageProvider implements CrawlFailureNotificationMessageProvider {
	/** @inheritdoc */
	public function title(): string {
		return __( 'baywall: RPC URL failure detected', 'baywall' );
	}

	/** @inheritdoc */
	public function message( string $chain_name, ChainId $chain_id ): string {
		return sprintf(
			/* translators: 1: chain name, 2: chain ID, 3: site URL */
			__(
				"Site: %3\$s\n\"%1\$s\" (Chain ID: %2\$d) is failing to connect.\nPlease update the RPC URL for this chain.",
				'baywall'
			),
			$chain_name,
			$chain_id->value(),
			site_url()
		);
	}
}
