<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Dto;

/** 販売履歴取得用の invoice 情報 */
class SalesHistoryInvoiceDto {
	public string $id;
	public int $created_at_unix;
	public int $post_id;
	public PriceDto $selling_price;
	public int $chain_id;

	public function __construct( string $invoice_id, int $created_at_unix, int $post_id, PriceDto $selling_price, int $chain_id ) {
		$this->id              = $invoice_id;
		$this->created_at_unix = $created_at_unix;
		$this->post_id         = $post_id;
		$this->selling_price   = $selling_price;
		$this->chain_id        = $chain_id;
	}
}
