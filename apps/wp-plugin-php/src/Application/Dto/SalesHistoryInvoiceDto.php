<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Dto;

/** 販売履歴取得用の invoice 情報 */
class SalesHistoryInvoiceDto {
	public string $id;
	public int $block_timestamp;
	public int $post_id;
	public PriceDto $selling_price;
	public int $chain_id;
	public string $chain_name;

	public function __construct( string $invoice_id, int $block_timestamp, int $post_id, PriceDto $selling_price, int $chain_id, string $chain_name ) {
		$this->id              = $invoice_id;
		$this->block_timestamp = $block_timestamp;
		$this->post_id         = $post_id;
		$this->selling_price   = $selling_price;
		$this->chain_id        = $chain_id;
		$this->chain_name      = $chain_name;
	}
}
