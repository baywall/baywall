<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

class Erc4361Nonce implements ValueObject {

	private Erc4361NonceString $nonce_string;
	private UnixTimestamp $issued_at;

	private function __construct( Erc4361NonceString $nonce_string, UnixTimestamp $issued_at ) {
		$this->nonce_string = $nonce_string;
		$this->issued_at    = $issued_at;
	}

	public static function from( Erc4361NonceString $nonce_string, UnixTimestamp $issued_at ): self {
		return new self( $nonce_string, $issued_at );
	}

	/** nonce文字列 */
	public function nonce(): Erc4361NonceString {
		return $this->nonce_string;
	}

	/** nonceが発行された日時 */
	public function issuedAt(): UnixTimestamp {
		return $this->issued_at;
	}

	public function __toString(): string {
		return (string) $this->nonce_string;
	}
}
