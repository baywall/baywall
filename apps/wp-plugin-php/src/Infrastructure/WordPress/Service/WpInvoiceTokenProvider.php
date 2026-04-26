<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpConfig;
use Cornix\Serendipity\Core\Domain\Service\InvoiceTokenProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\WpInvoiceTokenString;

class WpInvoiceTokenProvider implements InvoiceTokenProvider {

	/** @inheritdoc */
	public function generateInvoiceTokenString(): InvoiceTokenString {
		return WpInvoiceTokenString::generate();
	}

	/** @inheritdoc */
	public function getExpiresAt(): UnixTimestamp {
		return UnixTimestamp::from( time() + WpConfig::INVOICE_TOKEN_EXPIRATION_DURATION );
	}
}
