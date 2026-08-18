<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Domain\Service\InvoiceTokenProvider;
use Baywall\Core\Domain\ValueObject\InvoiceTokenString;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\ValueObject\WpInvoiceTokenString;

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
