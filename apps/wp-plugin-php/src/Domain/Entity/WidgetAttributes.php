<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

/** Gutenbergで配置したブロックの属性(画面で設定された値) */
class WidgetAttributes {

	private ?NetworkCategoryId $selling_network_category_id;
	private ?Amount $selling_amount;
	private ?Symbol $selling_symbol;

	private function __construct( ?NetworkCategoryId $selling_network_category_id, ?Amount $selling_amount, ?Symbol $selling_symbol ) {
		$this->selling_network_category_id = $selling_network_category_id;
		$this->selling_amount              = $selling_amount;
		$this->selling_symbol              = $selling_symbol;
	}

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
			Config::BLOCK_ATTR_NAME_SELLING_NETWORK_CATEGORY_ID => $this->selling_network_category_id ? $this->selling_network_category_id->value() : null,
			Config::BLOCK_ATTR_NAME_SELLING_AMOUNT => $this->selling_amount ? $this->selling_amount->value() : null,
			Config::BLOCK_ATTR_NAME_SELLING_SYMBOL => $this->selling_symbol ? $this->selling_symbol->value() : null,
		);
	}
}
