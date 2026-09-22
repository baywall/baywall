<?php
declare(strict_types=1);

namespace Baywall\Core\Application\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;

class Erc4361Nonce implements ValueObject {


	private function __construct(
		private Erc4361NonceString $nonce_string,
		private readonly UnixTimestamp $issued_at
	) {}

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
