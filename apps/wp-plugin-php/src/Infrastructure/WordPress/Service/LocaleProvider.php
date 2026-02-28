<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

class LocaleProvider {

	public function getLocale(): string {
		return get_locale();
	}

	/** 言語(`ja`,`en`等)を取得します */
	public function getLanguage(): string {
		return strtolower( explode( '_', $this->getLocale() )[0] );
	}

	/** 地域(`US`,`GB`等)を取得します */
	public function getRegion(): ?string {
		$parts = explode( '_', $this->getLocale() );
		if ( count( $parts ) === 2 && $this->isRegionFormat( $parts[1] ) ) {
			return $parts[1];
		} elseif ( count( $parts ) === 3 && $this->isRegionFormat( $parts[2] ) ) {
			return $parts[2];
		} else {
			return null;
		}
	}

	/** 地域コードのフォーマットかどうかを返します */
	private function isRegionFormat( string $region ): bool {
		return preg_match( '/^[A-Z]{2}$/', $region ) === 1;
	}
}
