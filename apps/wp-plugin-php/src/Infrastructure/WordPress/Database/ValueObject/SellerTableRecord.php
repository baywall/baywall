<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class SellerTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->seller_address       = (string) $record->seller_address;
		$record->agreed_terms_version = (int) $record->agreed_terms_version;
		$record->signing_message      = (string) $record->signing_message;
		$record->signature            = (string) $record->signature;

		$this->import( $record );
	}

	protected string $seller_address;
	protected int $agreed_terms_version;
	protected string $signing_message;
	protected string $signature;

	public function sellerAddressValue(): string {
		return $this->seller_address;
	}
	public function agreedTermsVersionValue(): int {
		return $this->agreed_terms_version;
	}
	public function signingMessageValue(): string {
		return $this->signing_message;
	}
	public function signatureValue(): string {
		return $this->signature;
	}
}
