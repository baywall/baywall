<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Entity\InvoiceToken;
use Cornix\Serendipity\Core\Infrastructure\Cookie\Cookie;

/**
 * 請求書トークンをCookieに書き込む際のプロパティを提供します
 */
interface InvoiceTokenCookieProvider {
	public function get( InvoiceToken $invoice_token ): Cookie;

	/** 請求書トークンをCookieから削除する際のプロパティを取得します */
	public function getExpired(): Cookie;
}
