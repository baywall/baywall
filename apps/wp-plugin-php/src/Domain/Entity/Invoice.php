<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Domain\ValueObject\Price;
use Baywall\Core\Domain\ValueObject\Symbol;

class Invoice {

	public function __construct(
		private readonly InvoiceId $id,
		private readonly PostId $post_id,
		private readonly ChainId $chain_id,
		private readonly Price $selling_price,
		private readonly Address $seller_address,
		private readonly Address $payment_token_address,
		private readonly Symbol $payment_token_symbol,
		private readonly Decimals $payment_token_decimals,
		private readonly Amount $payment_amount,
		private readonly Address $buyer_address
	) {}


	public function id(): InvoiceId {
		return $this->id;
	}
	public function postId(): PostId {
		return $this->post_id;
	}
	public function chainId(): ChainId {
		return $this->chain_id;
	}
	public function sellingPrice(): Price {
		return $this->selling_price;
	}
	public function sellerAddress(): Address {
		return $this->seller_address;
	}
	public function paymentTokenAddress(): Address {
		return $this->payment_token_address;
	}
	public function paymentTokenSymbol(): Symbol {
		return $this->payment_token_symbol;
	}
	public function paymentTokenDecimals(): Decimals {
		return $this->payment_token_decimals;
	}
	public function paymentAmount(): Amount {
		return $this->payment_amount;
	}
	public function buyerAddress(): Address {
		return $this->buyer_address;
	}

	public function __toString() {
		return (string) $this->id;
	}
}
