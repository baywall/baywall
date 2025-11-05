<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/** 記事の有料部分を表現するクラス */
class PaidContent extends Content {
	private function __construct( string $paid_content_text ) {
		parent::__construct( $paid_content_text );
	}

	public static function from( string $paid_content_text ): self {
		return new self( $paid_content_text );
	}
}
