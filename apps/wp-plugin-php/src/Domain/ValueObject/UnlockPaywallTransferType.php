<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

class UnlockPaywallTransferType implements ValueObject {

	// 以下の定義はコントラクトで定義されている内容と一致させるようにしてください。
	/** 販売手数料 */
	private const HANDLING_FEE = 1;
	/** 販売者の売上 */
	private const SELLER_PROFIT = 2;
	/** アフィリエイト報酬 */
	private const AFFILIATE_REWARD = 3;

	private function __construct( int $unlock_paywall_transfer_type_value ) {
		self::checkValidTransferTypeID( $unlock_paywall_transfer_type_value );
		$this->value = $unlock_paywall_transfer_type_value;
	}

	private int $value;

	public function value(): int {
		return $this->value;
	}

	public function __toString(): string {
		return (string) $this->value;
	}

	public function equals( self $other ): bool {
		return $this->value === $other->value;
	}

	public static function from( int $unlock_paywall_transfer_type_value ): self {
		return new self( $unlock_paywall_transfer_type_value );
	}

	private static function checkValidTransferTypeID( int $unlock_paywall_transfer_type_value ): void {
		if ( $unlock_paywall_transfer_type_value < self::HANDLING_FEE || $unlock_paywall_transfer_type_value > self::AFFILIATE_REWARD ) {
			throw new \InvalidArgumentException( '[F468C1FA] Invalid unlock paywall transfer type ID: ' . $unlock_paywall_transfer_type_value );
		}
	}
}
