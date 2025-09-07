<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Repository\NetworkCategoryRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\NetworkCategoryIdConstants;
use Cornix\Serendipity\Core\Repository\I18nText;

class NetworkCategoryRepositoryImpl implements NetworkCategoryRepository {

	private I18nText $i18n;

	public function __construct( I18nText $i18n ) {
		$this->i18n = $i18n;
	}

	/** @inheritdoc */
	public function all(): array {
		return array_map(
			fn( NetworkCategoryId $network_category_id ) => $this->get( $network_category_id ),
			NetworkCategoryIdConstants::all()
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
