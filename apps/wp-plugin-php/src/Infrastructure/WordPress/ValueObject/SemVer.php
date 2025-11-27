<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

/**
 * セマンティックバージョンを表す値オブジェクト
 */
class SemVer {

	protected function __construct( string $sem_ver_value ) {
		$this->checkSemVerValue( $sem_ver_value );
		$this->sem_ver_value = $sem_ver_value;
	}
	private string $sem_ver_value;

	public function value(): string {
		return $this->sem_ver_value;
	}

	public static function from( string $sem_ver_value ): self {
		return new self( $sem_ver_value );
	}

	public function equals( self $other ): bool {
		return $this->value() === $other->value();
	}

	public function __toString(): string {
		return $this->sem_ver_value;
	}

	public function compareOperator( self $other, string $operator ): bool {
		return version_compare( $this->sem_ver_value, $other->sem_ver_value, $operator );
	}

	private function checkSemVerValue( string $sem_ver_value ): void {
		$sem_ver2_pattern = '/^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';
		if ( ! preg_match( $sem_ver2_pattern, $sem_ver_value ) ) {
			throw new \InvalidArgumentException( "[CA8A7D1E] Invalid semantic version format. {$sem_ver_value}" );
		}
	}
}
