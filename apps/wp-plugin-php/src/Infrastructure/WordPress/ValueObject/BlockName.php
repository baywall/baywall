<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

/**
 * Gutenbergのブロック名を表す値オブジェクト
 */
class BlockName {

	private function __construct( string $block_name_value ) {
		$this->checkBlockNameValue( $block_name_value );
		$this->block_name_value = $block_name_value;
	}
	private string $block_name_value;

	public function value(): string {
		return $this->block_name_value;
	}

	public static function from( string $block_name ): self {
		return new self( $block_name );
	}

	public function equals( self $other ): bool {
		return $this->block_name_value === $other->block_name_value;
	}

	public function __toString(): string {
		return $this->block_name_value;
	}

	private function checkBlockNameValue( string $block_name_value ): void {
		// ブロック名はスラッシュで区切られた形式、またはスラッシュなしの形式
		if ( ! preg_match( '/^[a-zA-Z0-9-]+(\/[a-zA-Z0-9-]+)?$/', $block_name_value ) ) {
			throw new \InvalidArgumentException( "[E09843A5] Invalid block name format. {$block_name_value}" );
		}
	}
}
