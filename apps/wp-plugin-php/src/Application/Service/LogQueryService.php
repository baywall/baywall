<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Dto\LogDto;

/**
 * ログ取得Serviceのインターフェース
 */
interface LogQueryService {

	/**
	 * 最近のログを取得します。
	 *
	 * @param int $limit 取得件数の上限
	 * @return LogDto[]
	 */
	public function findRecent( int $limit ): array;
}
