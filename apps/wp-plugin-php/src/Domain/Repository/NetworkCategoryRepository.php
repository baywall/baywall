<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;

interface NetworkCategoryRepository {
	/**
	 * ネットワークカテゴリ一覧を取得します。
	 *
	 * @return NetworkCategory[]
	 */
	public function all(): array;

	/** 指定したネットワークカテゴリIDに一致するネットワークカテゴリ情報を取得します。 */
	public function get( NetworkCategoryId $network_category_id ): ?NetworkCategory;
}
