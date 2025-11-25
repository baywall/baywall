<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\InvoiceToken;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;

interface InvoiceTokenRepository {
	/** 指定した請求書トークン文字列に合致する請求書情報を取得します。 */
	public function get( InvoiceTokenString $invoice_token_string ): ?InvoiceToken;

	/** 請求書トークン情報を追加します。 */
	public function add( InvoiceToken $invoice_token ): void;

	/** 請求書トークン情報を更新します。 */
	public function update( InvoiceToken $invoice_token ): void;
}
