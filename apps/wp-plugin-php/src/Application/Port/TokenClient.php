<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Domain\ValueObject\Symbol;

interface TokenClient {
	/** トークンの小数点以下桁数を取得します */
	public function decimals(): Decimals;

	/** トークンの通貨シンボルを取得します */
	public function symbol(): Symbol;
}
