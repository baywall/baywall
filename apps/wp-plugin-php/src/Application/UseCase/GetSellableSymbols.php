<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Service\SellableSymbolsProvider;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\Symbol;

class GetSellableSymbols {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly SellableSymbolsProvider $sellable_symbols_provider
	) {}

	/**
	 * 対象のネットワークカテゴリで販売可能なシンボル一覧を取得します。
	 *
	 * @return Symbol[]
	 */
	public function handle( NetworkCategoryId $network_category_id ): array {
		$this->user_access_checker->checkCanCreatePost();   // 投稿を新規作成できる権限が必要

		return $this->sellable_symbols_provider->sellableSymbols( $network_category_id );
	}
}
