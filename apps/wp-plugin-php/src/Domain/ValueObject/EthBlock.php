<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

/**
 * ブロック情報を表すクラス。
 *
 * `ValueObject` インタフェースは実装していません。複合フィールドのため `__toString` の
 * 意味論が定まらないこと、`OracleRoundData` と同様に扱うためです。
 */
class EthBlock {

	private function __construct(
		private readonly BlockNumber $block_number,
		private readonly UnixTimestamp $timestamp
	) {}

	public static function from( BlockNumber $block_number, UnixTimestamp $timestamp ): self {
		return new self( $block_number, $timestamp );
	}

	public function number(): BlockNumber {
		return $this->block_number;
	}

	public function timestamp(): UnixTimestamp {
		return $this->timestamp;
	}
}
