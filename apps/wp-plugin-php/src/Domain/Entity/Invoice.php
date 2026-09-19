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

	public function __construct( InvoiceId $id, PostId $post_id, ChainId $chain_id, Price $selling_price, Address $seller_address, Address $payment_token_address, Symbol $payment_token_symbol, Decimals $payment_token_decimals, Amount $payment_amount, Address $buyer_address ) {
		$this->id                     = $id;
		$this->post_id                = $post_id;
		$this->chain_id               = $chain_id;
		$this->selling_price          = $selling_price;
		$this->seller_address         = $seller_address;
		$this->payment_token_address  = $payment_token_address;
		$this->payment_token_symbol   = $payment_token_symbol;
		$this->payment_token_decimals = $payment_token_decimals;
		$this->payment_amount         = $payment_amount;
		$this->buyer_address          = $buyer_address;
	}

	private InvoiceId $id;
	private PostId $post_id;
	private ChainId $chain_id;
	private Price $selling_price;
	private Address $seller_address;
	private Address $payment_token_address;
	private Symbol $payment_token_symbol;
	private Decimals $payment_token_decimals;
	private Amount $payment_amount;
	private Address $buyer_address;

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
