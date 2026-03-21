<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository\SearchCondition;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class InvoiceSearchCondition {

	private ?InvoiceId $invoice_id     = null;
	private ?PostId $post_id           = null;
	private ?Address $customer_address = null;

	/** 請求書IDを取得します */
	public function invoiceId(): ?InvoiceId {
		return $this->invoice_id;
	}
	/** 請求書IDを設定します */
	public function setInvoiceId( ?InvoiceId $invoice_id ): self {
		$this->invoice_id = $invoice_id;
		return $this;
	}

	/** 投稿IDを取得します */
	public function postId(): ?PostId {
		return $this->post_id;
	}
	/** 投稿IDを設定します */
	public function setPostId( ?PostId $post_id ): self {
		$this->post_id = $post_id;
		return $this;
	}

	/** 購入者アドレスを取得します */
	public function customerAddress(): ?Address {
		return $this->customer_address;
	}
	/** 購入者アドレスを設定します */
	public function setCustomerAddress( ?Address $customer_address ): self {
		$this->customer_address = $customer_address;
		return $this;
	}
}
