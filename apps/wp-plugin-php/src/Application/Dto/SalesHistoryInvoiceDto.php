<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Dto;

/** 販売履歴取得用の invoice 情報 */
class SalesHistoryInvoiceDto {
	public readonly string $id;
	public readonly int $block_timestamp;
	public readonly int $post_id;
	public readonly PriceDto $selling_price;
	public readonly int $chain_id;
	public readonly string $chain_name;

	public function __construct( string $invoice_id, int $block_timestamp, int $post_id, PriceDto $selling_price, int $chain_id, string $chain_name ) {
		$this->id              = $invoice_id;
		$this->block_timestamp = $block_timestamp;
		$this->post_id         = $post_id;
		$this->selling_price   = $selling_price;
		$this->chain_id        = $chain_id;
		$this->chain_name      = $chain_name;
	}
}
