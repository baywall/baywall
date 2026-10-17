<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\Clock;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Domain\Service\InvoiceTokenProvider;
use Baywall\Core\Domain\ValueObject\InvoiceTokenString;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\ValueObject\WpInvoiceTokenString;

class WpInvoiceTokenProvider implements InvoiceTokenProvider {

	public function __construct( private readonly Clock $clock ) {}

	/** @inheritdoc */
	public function generateInvoiceTokenString(): InvoiceTokenString {
		return WpInvoiceTokenString::generate();
	}

	/** @inheritdoc */
	public function getExpiresAt(): UnixTimestamp {
		return $this->clock->now()->addSeconds( WpConfig::INVOICE_TOKEN_EXPIRATION_DURATION );
	}
}
