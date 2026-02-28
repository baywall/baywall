<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Dto\SalesHistoryDto;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

/**
 * 売上データを取得するクラス
 */
interface SalesHistoryService {

	/**
	 * 条件に合致する販売履歴を取得します。
	 *
	 * @return SalesHistoryDto[]
	 */
	public function find( ?InvoiceId $filter_invoice_id ): array;
}
