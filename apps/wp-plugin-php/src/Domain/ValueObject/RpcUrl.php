<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\UrlBase;
use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * RPC URLを表すValueObjectクラス
 */
final class RpcUrl extends UrlBase implements ValueObject {

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

	public function equals( self $other ): bool {
		return $this->value() === $other->value();
	}
}
