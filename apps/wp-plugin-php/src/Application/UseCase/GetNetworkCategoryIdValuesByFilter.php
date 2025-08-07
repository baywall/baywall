<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;

class GetNetworkCategoryIdValuesByFilter {

	private ChainRepository $chain_repository;

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	/**
	 * ネットワークカテゴリID一覧を取得します
	 *
	 * @param int|null $filter_network_category ネットワークカテゴリIDのフィルタ条件
	 *
	 * @return int[] ネットワークカテゴリIDの配列（空配列の可能性あり）
	 */
	public function handle( ?int $filter_network_category ): array {
		NetworkCategoryId::fromNullable( $filter_network_category ); // 引数チェック

		// 登録されているチェーンの情報から、ネットワークID一覧を取得
		$network_category_values = array_unique(
			array_map(
				fn ( Chain $chain ) => $chain->networkCategoryId()->value(),
				$this->chain_repository->all()
			)
		);

		// ネットワークカテゴリのIDがフィルタ条件で指定されている場合は、そのIDに絞り込み
		if ( $filter_network_category !== null ) {
			$network_category_values = array_filter(
				$network_category_values,
				fn ( int $network_category_id ) => $network_category_id === $filter_network_category
			);
		}

		return $network_category_values;
	}
}
