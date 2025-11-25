<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceTokenRepository;
use Cornix\Serendipity\Core\Domain\Service\InvoiceTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\WpInvoiceTokenString;

class WpInvoiceTokenService extends InvoiceTokenService {

	public function __construct( InvoiceTokenRepository $invoice_token_repository ) {
		parent::__construct( $invoice_token_repository );
	}

	/** @inheritdoc */
	protected function generateInvoiceTokenString(): InvoiceTokenString {
		return WpInvoiceTokenString::generate();
	}

	/** @inheritdoc */
	protected function getExpiresAt(): UnixTimestamp {
		return UnixTimestamp::from( time() + WpConfig::INVOICE_TOKEN_EXPIRATION_DURATION );
	}
}
