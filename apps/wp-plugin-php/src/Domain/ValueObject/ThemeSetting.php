<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * サイトのテーマ設定(`auto` / `light` / `dark`)を表すValueObjectクラス
 *
 * - `auto`: OSのダークモード設定に従う(ウィジェットには `data-baywall-theme` 属性は出力されない)
 * - `light`: ライトモード
 * - `dark`: ダークモード
 */
final class ThemeSetting implements ValueObject {
	public const AUTO  = 'auto';
	public const LIGHT = 'light';
	public const DARK  = 'dark';

	private string $theme;

	private function __construct( string $theme ) {
		if ( ! in_array( $theme, array( self::AUTO, self::LIGHT, self::DARK ), true ) ) {
			throw new \InvalidArgumentException( '[C1D3AFED] Invalid theme setting: ' . $theme );
		}

		$this->theme = $theme;
	}

	public function value(): string {
		return $this->theme;
	}

	public function isAuto(): bool {
		return $this->theme === self::AUTO;
	}

	public function equals( self $other ): bool {
		return $this->theme === $other->theme;
	}

	public function __toString(): string {
		return $this->theme;
	}

	public static function auto(): self {
		return new self( self::AUTO );
	}

	public static function light(): self {
		return new self( self::LIGHT );
	}

	public static function dark(): self {
		return new self( self::DARK );
	}

	public static function from( string $theme ): self {
		return new self( $theme );
	}
}
