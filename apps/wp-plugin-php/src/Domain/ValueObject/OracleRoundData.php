<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use phpseclib4\Math\BigInteger;

/**
 * `latestRoundData` の戻り値を保持するクラス。
 *
 * `ValueObject` インタフェースは実装していません。複合フィールドのため `__toString` の
 * 意味論が定まらないこと、`BigInteger` の ValueObject 化とあわせて後日見直すためです。
 */
class OracleRoundData {
	public function __construct( BigInteger $round_id, BigInteger $answer, BigInteger $started_at, BigInteger $updated_at, BigInteger $answered_in_round ) {
		$this->round_id          = $round_id;
		$this->answer            = $answer;
		$this->started_at        = $started_at;
		$this->updated_at        = $updated_at;
		$this->answered_in_round = $answered_in_round;
	}

	private readonly BigInteger $round_id;
	private readonly BigInteger $answer;
	private readonly BigInteger $started_at;
	private readonly BigInteger $updated_at;
	private readonly BigInteger $answered_in_round;

	public function roundId(): BigInteger {
		return $this->round_id;
	}

	public function answer(): BigInteger {
		return $this->answer;
	}

	public function startedAt(): BigInteger {
		return $this->started_at;
	}

	public function updatedAt(): BigInteger {
		return $this->updated_at;
	}

	public function answeredInRound(): BigInteger {
		return $this->answered_in_round;
	}
}
