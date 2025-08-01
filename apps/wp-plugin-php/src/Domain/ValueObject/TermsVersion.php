<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/** 利用規約バージョン */
class TermsVersion {
	private function __construct( int $version_value ) {
		assert( $version_value > 0, '[1AA762E9] TermsVersion must be greater than 0' );
		$this->version_value = $version_value;
	}
	public static function from( int $version_value ): self {
		return new self( $version_value );
	}

	private int $version_value;

	public function value(): int {
		return $this->version_value;
	}

	public function equals( self $other ): bool {
		return $this->version_value === $other->value();
	}
}
