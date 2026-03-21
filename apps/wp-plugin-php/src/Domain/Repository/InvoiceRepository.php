<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Repository\SearchCondition\InvoiceSearchCondition;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

interface InvoiceRepository {
	/** 指定した請求書IDに合致する請求書情報を取得します。 */
	public function get( InvoiceId $invoice_id ): ?Invoice;

	/**
	 * 指定した条件に合致する請求書情報を取得します。
	 *
	 * @param InvoiceSearchCondition $condition 検索条件
	 * @return Invoice[] 条件に合致する請求書情報の配列
	 */
	public function findBy( InvoiceSearchCondition $condition ): array;

	/** 請求書情報を保存します。 */
	public function save( Invoice $invoice ): void;
}
