<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class Erc4361NonceTableRecord extends TableRecordBase {

	protected string $erc4361_nonce;
	protected string $wallet_address;
	protected int $issued_at;

	public function __construct( stdClass $record ) {
		$record->erc4361_nonce  = (string) $record->erc4361_nonce;
		$record->wallet_address = (string) $record->wallet_address;
		$record->issued_at      = (int) $record->issued_at;
		$this->import( $record );
	}

	public function erc4361NonceValue(): string {
		return $this->erc4361_nonce;
	}
	public function walletAddressValue(): string {
		return $this->wallet_address;
	}
	public function issuedAtValue(): int {
		return $this->issued_at;
	}
}
