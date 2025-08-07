<?php

namespace Cornix\Serendipity\Core\Application\Dto;

class IssuedInvoiceDto {

	public function __construct( string $id_hex, string $nonce, string $server_message, string $server_signature, string $payment_amount ) {
		$this->id_hex           = $id_hex;
		$this->nonce            = $nonce;
		$this->server_message   = $server_message;
		$this->server_signature = $server_signature;
		$this->payment_amount   = $payment_amount;
	}

	public string $id_hex;
	public string $nonce;
	public string $server_message;
	public string $server_signature;
	public string $payment_amount;
}
