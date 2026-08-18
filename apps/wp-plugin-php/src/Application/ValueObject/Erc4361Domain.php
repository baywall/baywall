<?php
declare(strict_types=1);

namespace Baywall\Core\Application\ValueObject;

use Baywall\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * ERC-4361 Domain 文字列を表すクラス
 */
class Erc4361Domain extends StringValueObject {

	protected function __construct( string $erc4361_domain_value ) {
		parent::__construct( $erc4361_domain_value );
	}

	public static function from( string $erc4361_domain_value ): self {
		return new self( $erc4361_domain_value );
	}
}
