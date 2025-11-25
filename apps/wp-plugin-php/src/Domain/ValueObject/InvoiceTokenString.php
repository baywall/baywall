<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * 請求書トークン文字列を表すクラス
 *
 * - 請求書トークン文字列のフォーマットは実装次第のため、ここではチェックしない
 */
class InvoiceTokenString extends StringValueObject {
	protected function __construct( string $invoice_token_value ) {
		parent::__construct( $invoice_token_value );
	}

	public static function from( string $invoice_token_value ): self {
		return new self( $invoice_token_value );
	}
}
