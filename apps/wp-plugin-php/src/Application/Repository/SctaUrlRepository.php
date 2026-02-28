<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Repository;

use Cornix\Serendipity\Core\Domain\ValueObject\SctaUrl;

interface SctaUrlRepository {

	/** 「特定商取引法に基づく表記」のURLを取得します */
	public function get(): ?SctaUrl;

	/** 「特定商取引法に基づく表記」のURLを保存します */
	public function save( ?SctaUrl $scta_url ): void;
}
