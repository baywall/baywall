<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\Symbol;

/** Gutenbergで配置したブロックの属性(画面で設定された値) */
class WidgetAttributes {


	private function __construct(
		private readonly ?NetworkCategoryId $selling_network_category_id,
		private readonly ?Amount $selling_amount,
		private readonly ?Symbol $selling_symbol
	) {}

	public static function from( ?NetworkCategoryId $selling_network_category_id, ?Amount $selling_amount, ?Symbol $selling_symbol ): self {
		return new self( $selling_network_category_id, $selling_amount, $selling_symbol );
	}

	public function sellingNetworkCategoryId(): ?NetworkCategoryId {
		return $this->selling_network_category_id;
	}
	public function sellingAmount(): ?Amount {
		return $this->selling_amount;
	}
	public function sellingSymbol(): ?Symbol {
		return $this->selling_symbol;
	}

	public function toArray(): array {
		return array(
			WpConfig::BLOCK_ATTR_NAME_SELLING_NETWORK_CATEGORY_ID => $this->selling_network_category_id?->value(),
			WpConfig::BLOCK_ATTR_NAME_SELLING_AMOUNT => $this->selling_amount?->value(),
			WpConfig::BLOCK_ATTR_NAME_SELLING_SYMBOL => $this->selling_symbol?->value(),
		);
	}

	public static function fromArray( array $attrs ): self {
		return self::from(
			NetworkCategoryId::fromNullable( $attrs[ WpConfig::BLOCK_ATTR_NAME_SELLING_NETWORK_CATEGORY_ID ] ?? null ),
			Amount::fromNullable( $attrs[ WpConfig::BLOCK_ATTR_NAME_SELLING_AMOUNT ] ?? null ),
			Symbol::fromNullable( $attrs[ WpConfig::BLOCK_ATTR_NAME_SELLING_SYMBOL ] ?? null )
		);
	}
}
