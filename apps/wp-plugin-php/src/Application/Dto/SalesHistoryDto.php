<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Dto;

class SalesHistoryDto {

	public SalesHistoryInvoiceDto $invoice;
	public string $post_title;
	public string $tx_hash;
	public string $customer_address;
	public PriceDto $customer_payment_price;
	public string $contract_address;
	public PriceDto $contract_received_price;
	public string $seller_address;
	public PriceDto $seller_received_price;
	public ?string $affiliate_address;
	public ?PriceDto $affiliate_received_price;

	public function __construct(
		SalesHistoryInvoiceDto $invoice,
		string $post_title,
		string $tx_hash,
		string $customer_address,
		PriceDto $customer_payment_price,
		string $contract_address,
		PriceDto $contract_received_price,
		string $seller_address,
		PriceDto $seller_received_price,
		?string $affiliate_address,
		?PriceDto $affiliate_received_price
	) {
		$this->invoice                  = $invoice;
		$this->post_title               = $post_title;
		$this->tx_hash                  = $tx_hash;
		$this->customer_address         = $customer_address;
		$this->customer_payment_price   = $customer_payment_price;
		$this->contract_address         = $contract_address;
		$this->contract_received_price  = $contract_received_price;
		$this->seller_address           = $seller_address;
		$this->seller_received_price    = $seller_received_price;
		$this->affiliate_address        = $affiliate_address;
		$this->affiliate_received_price = $affiliate_received_price;
	}
}
