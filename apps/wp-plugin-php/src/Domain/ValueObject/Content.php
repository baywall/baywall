<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/** 記事の内容を表現するクラス */
class Content implements ValueObject {
	protected function __construct( string $content_text ) {
		$this->content_text = $content_text;
	}
	private string $content_text;

	public static function from( string $content_text ): self {
		return new self( $content_text );
	}

	public function value(): string {
		return $this->content_text;
	}

	public function __toString(): string {
		return $this->content_text;
	}

	public function equals( self $other ): bool {
		return $this->content_text === $other->content_text;
	}
}
