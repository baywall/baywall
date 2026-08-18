<?php
declare(strict_types=1);
namespace Baywall\Core\Infrastructure\Cache;

use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\ValueObject\Rate;

interface OracleRateCache {

	/** 対象となるオラクルから取得したレートをキャッシュとして保存します */
	public function set( Oracle $oracle, Rate $rate ): void;

	/** 対象となるオラクルのレートをキャッシュから取得します */
	public function get( Oracle $oracle ): ?Rate;
}
