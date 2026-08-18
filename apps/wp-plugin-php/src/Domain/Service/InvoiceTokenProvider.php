<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\ValueObject\InvoiceTokenString;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;

interface InvoiceTokenProvider {
	/** 請求書トークン文字列を生成します */
	public function generateInvoiceTokenString(): InvoiceTokenString;

	/** 請求書トークンの有効期限を取得します */
	public function getExpiresAt(): UnixTimestamp;
}
