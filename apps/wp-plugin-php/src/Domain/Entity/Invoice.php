<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;

class Invoice {

	public function __construct( InvoiceID $id, PostId $post_id, ChainId $chain_id, Price $selling_price, Address $seller_address, Address $payment_token_address, Amount $payment_amount, Address $consumer_address, ?InvoiceNonce $nonce = null ) {
		$this->id                    = $id;
		$this->post_id               = $post_id;
		$this->chain_id              = $chain_id;
		$this->selling_price         = $selling_price;
		$this->seller_address        = $seller_address;
		$this->payment_token_address = $payment_token_address;
		$this->payment_amount        = $payment_amount;
		$this->consumer_address      = $consumer_address;
		$this->nonce                 = $nonce;
	}

	private InvoiceID $id;
	private PostId $post_id;
	private ChainId $chain_id;
	private Price $selling_price;
	private Address $seller_address;
	private Address $payment_token_address;
	private Amount $payment_amount;
	private Address $consumer_address;
	private ?InvoiceNonce $nonce;

	public function id(): InvoiceID {
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
	public function paymentAmount(): Amount {
		return $this->payment_amount;
	}
	public function consumerAddress(): Address {
		return $this->consumer_address;
	}
	public function nonce(): ?InvoiceNonce {
		return $this->nonce;
	}
}
