<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\ValueObject\UnixTimestamp;

/**
 * 現在時刻の取得を提供する最小のClock抽象。
 *
 * 時刻の取得は境界層（Application / Infrastructure）に限定し、Domain層には判定に必要な`UnixTimestamp`を引数で渡す。
 */
interface Clock {
	public function now(): UnixTimestamp;
}
