<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/** 署名済み利用規約情報 */
class SignedTerms {
	private function __construct( Terms $terms, Signature $signature ) {
		$this->terms     = $terms;
		$this->signature = $signature;
	}
	public static function from( Terms $terms, Signature $signature ): self {
		return new self( $terms, $signature );
	}

	private Terms $terms;
	private Signature $signature;

	public function terms(): Terms {
		return $this->terms;
	}

	public function signature(): Signature {
		return $this->signature;
	}
}
