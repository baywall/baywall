<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Repository\NetworkCategoryRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\NetworkCategoryIdConstants;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\I18nTextProvider;

class NetworkCategoryRepositoryImpl implements NetworkCategoryRepository {

	private ChainTable $chain_table;
	private I18nTextProvider $i18n;

	public function __construct( ChainTable $chain_table, I18nTextProvider $i18n ) {
		$this->chain_table = $chain_table;
		$this->i18n        = $i18n;
	}

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

		return array_map(
			fn( int $network_category_id_value ) => $this->get( NetworkCategoryId::from( $network_category_id_value ) ),
			array_values( $network_category_id_values )
		);
	}

	/** @inheritdoc */
	public function get( NetworkCategoryId $network_category_id ): ?NetworkCategory {
		return NetworkCategory::from( $network_category_id, $this->getName( $network_category_id ) );
	}

	private function getName( NetworkCategoryId $network_category_id ): string {
		$map = array(
			NetworkCategoryIdConstants::mainnet()->value() => fn() => $this->i18n->mainnet(),
			NetworkCategoryIdConstants::testnet()->value() => fn() => $this->i18n->testnet(),
			NetworkCategoryIdConstants::privatenet()->value() => fn() => $this->i18n->privatenet(),
		);

		if ( ! isset( $map[ $network_category_id->value() ] ) ) {
			throw new \InvalidArgumentException( "[BD8EA728] Unknown NetworkCategoryId: {$network_category_id}" );
		}

		return $map[ $network_category_id->value() ]();
	}
}
