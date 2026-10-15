<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity;

use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\Symbol;

/** 販売設定(ペイウォールブロックで画面設定された値) */
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
}
