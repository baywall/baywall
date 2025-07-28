<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

class UnlockPaywallTransferType {

	// 以下の定義はコントラクトで定義されている内容と一致させるようにしてください。
	/** 販売手数料 */
	private const HANDLING_FEE = 1;
	/** 販売者の売上 */
	private const SELLER_PROFIT = 2;
	/** アフィリエイト報酬 */
	private const AFFILIATE_REWARD = 3;

	private function __construct( int $unlock_paywall_transfer_type_id ) {
		self::checkValidTransferTypeID( $unlock_paywall_transfer_type_id );
		$this->id = $unlock_paywall_transfer_type_id;
	}

	private int $id;

	public function id(): int {
		return $this->id;
	}

	public static function from( int $unlock_paywall_transfer_type_id ): self {
		return new self( $unlock_paywall_transfer_type_id );
	}

	private static function checkValidTransferTypeID( int $unlock_paywall_transfer_type_id ): void {
		if ( $unlock_paywall_transfer_type_id < self::HANDLING_FEE || $unlock_paywall_transfer_type_id > self::AFFILIATE_REWARD ) {
			throw new \InvalidArgumentException( '[F468C1FA] Invalid unlock paywall transfer type ID: ' . $unlock_paywall_transfer_type_id );
		}
	}
}
