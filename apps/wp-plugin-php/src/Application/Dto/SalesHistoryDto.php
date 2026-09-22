<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Dto;

class SalesHistoryDto {


	public function __construct(
		public readonly SalesHistoryInvoiceDto $invoice,
		public readonly string $post_title,
		public readonly string $tx_hash,
		public readonly string $buyer_address,
		public readonly PriceDto $buyer_payment_price,
		public readonly string $contract_address,
		public readonly PriceDto $contract_received_price,
		public readonly string $seller_address,
		public readonly PriceDto $seller_received_price,
		public readonly ?string $affiliate_address,
		public readonly ?PriceDto $affiliate_received_price
	) {}
}
