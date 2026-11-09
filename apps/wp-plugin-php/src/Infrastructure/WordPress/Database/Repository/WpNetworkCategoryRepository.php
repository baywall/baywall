<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Application\Repository\NetworkCategoryRepository;
use Baywall\Core\Domain\ValueObject\NetworkCategory;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\ChainTable;
use Baywall\Core\Infrastructure\WordPress\I18n\I18nTextProvider;

class WpNetworkCategoryRepository implements NetworkCategoryRepository {
	public function __construct(
		private readonly ChainTable $chain_table,
		private I18nTextProvider $i18n_text_provider
	) {}

	/** @inheritdoc */
	public function all(): array {
		// 登録済みのチェーンID一覧を取得
		// ※ DBから情報を取得することにより、Privatenetが開発環境のみで扱われるようになる
		$chains                     = $this->chain_table->all();
		$network_category_id_values = array_unique(
			array_map(
				fn( $chain ) => $chain->networkCategoryIdValue(),
				$chains
			)
		);

		$network_categories = array_map(
			fn( int $network_category_id_value ) => $this->get( NetworkCategoryId::from( $network_category_id_value ) ),
			array_values( $network_category_id_values )
		);

		// get() は定義上戻り値が nullable なので null 要素を除外する処理を実施
		return array_values( array_filter( $network_categories ) );
	}

	/** @inheritdoc */
	public function get( NetworkCategoryId $network_category_id ): ?NetworkCategory {
		return NetworkCategory::from( $network_category_id, $this->getName( $network_category_id ) );
	}

	private function getName( NetworkCategoryId $network_category_id ): string {
		$map = array(
			NetworkCategoryId::MAINNET    => fn() => $this->i18n_text_provider->mainnet(),
			NetworkCategoryId::TESTNET    => fn() => $this->i18n_text_provider->testnet(),
			NetworkCategoryId::PRIVATENET => fn() => $this->i18n_text_provider->privatenet(),
		);

		if ( ! isset( $map[ $network_category_id->value() ] ) ) {
			throw new \InvalidArgumentException( "[BD8EA728] Unknown NetworkCategoryId: {$network_category_id}" );
		}

		return $map[ $network_category_id->value() ]();
	}
}
