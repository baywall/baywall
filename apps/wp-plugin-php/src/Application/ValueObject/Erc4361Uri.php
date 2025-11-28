<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * ERC-4361 URI 文字列を表すクラス
 */
class Erc4361Uri extends StringValueObject {

	protected function __construct( string $erc4361_uri_value ) {
		parent::__construct( $erc4361_uri_value );
	}

	public static function from( string $erc4361_uri_value ): self {
		return new self( $erc4361_uri_value );
	}
}
