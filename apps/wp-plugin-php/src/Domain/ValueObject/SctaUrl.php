<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\UrlBase;
use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * 「特定商取引法に基づく表記」のURLを表すValueObjectクラス
 */
final class SctaUrl extends UrlBase implements ValueObject {

	private function __construct( string $scta_url ) {
		parent::__construct( $scta_url );
	}

	public static function from( string $scta_url ): self {
		return new self( $scta_url );
	}
	public static function fromNullable( ?string $scta_url ): ?self {
		if ( $scta_url === null ) {
			return null;
		}
		return new self( $scta_url );
	}

	public function equals( self $other ): bool {
		return $this->value() === $other->value();
	}
}
