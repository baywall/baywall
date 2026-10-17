<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\System;

use Baywall\Core\Application\Service\Clock;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;

/** 本番環境のClock実装。`time()`の呼び出しはこのクラスに集約する */
class SystemClock implements Clock {

	public function now(): UnixTimestamp {
		return UnixTimestamp::from( time() );
	}
}
