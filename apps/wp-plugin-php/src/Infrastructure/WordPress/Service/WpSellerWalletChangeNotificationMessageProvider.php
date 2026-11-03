<?php
declare(strict_types=1);
namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Port\SellerWalletChangeNotificationMessageProvider;
use Baywall\Core\Domain\ValueObject\Address;

class WpSellerWalletChangeNotificationMessageProvider implements SellerWalletChangeNotificationMessageProvider {
	/** @inheritdoc */
	public function title(): string {
		return __( 'baywall: Seller wallet address changed', 'baywall' );
	}

	/** @inheritdoc */
	public function message( Address $previous_address, Address $new_address ): string {
		return sprintf(
			/* translators: 1: previous address, 2: new address, 3: site URL */
			__(
				"Site: %3\$s\nThe seller wallet address has been changed.\nOld: %1\$s\nNew: %2\$s",
				'baywall'
			),
			$previous_address->value(),
			$new_address->value(),
			site_url()
		);
	}
}
