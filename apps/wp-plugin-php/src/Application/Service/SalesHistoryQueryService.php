<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Dto\SalesHistoryDto;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

/**
 * 売上データを取得するクラス
 */
interface SalesHistoryQueryService {

	/**
	 * 条件に合致する販売履歴を取得します。
	 *
	 * @return SalesHistoryDto[]
	 */
	public function find( ?InvoiceId $filter_invoice_id ): array;

	/**
	 * 指定された投稿IDと購入者アドレスに対応する販売履歴が存在するかどうかを取得します。
	 */
	public function existsByPostIdAndCustomerAddress( PostId $post_id, Address $customer_address ): bool;
}
