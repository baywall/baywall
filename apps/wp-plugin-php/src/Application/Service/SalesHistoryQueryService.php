<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\Dto\SalesHistoryDto;
use Baywall\Core\Domain\Repository\SearchCondition\SalesHistorySearchCondition;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\PostId;

/**
 * 売上データを取得するクラス
 */
interface SalesHistoryQueryService {

	/**
	 * 条件に合致する販売履歴を取得します。
	 *
	 * @param SalesHistorySearchCondition $condition 検索条件
	 * @return SalesHistoryDto[]
	 */
	public function find( SalesHistorySearchCondition $condition ): array;

	/**
	 * 指定された投稿IDと購入者アドレスに対応する販売履歴が存在するかどうかを取得します。
	 */
	public function existsByPostIdAndBuyerAddress( PostId $post_id, Address $buyer_address ): bool;
}
