<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

/**
 * RPC URLを表すValueObjectクラス
 */
final class RpcUrl extends Url {

	private function __construct( string $rpd_url ) {
		parent::__construct( $rpd_url );
	}

	public static function from( string $rpd_url ): self {
		return new self( $rpd_url );
	}
	public static function fromNullable( ?string $rpd_url ): ?self {
		if ( $rpd_url === null ) {
			return null;
		}
		return new self( $rpd_url );
	}
}
