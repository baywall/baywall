<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\NetworkCategoryIdConstants;

/**
 * ネットワークカテゴリIDを表すクラス
 */
final class NetworkCategoryId implements ValueObject {
	private function __construct( int $network_category_id_value ) {
		$this->checkValue( $network_category_id_value );
		$this->value = $network_category_id_value;
	}

	/** ネットワークカテゴリID(数値) */
	private int $value;

	/** ネットワークカテゴリIDを数値で取得します。 */
	public function value(): int {
		return $this->value;
	}

	public static function from( int $network_category_id_value ): self {
		return new self( $network_category_id_value );
	}

	/**
	 * ネットワークカテゴリID(数値)からインスタンスを取得します。
	 * 引数がnullの場合はnullを返します。
	 */
	public static function fromNullable( ?int $network_category_id_value ): ?self {
		return is_null( $network_category_id_value ) ? null : new self( $network_category_id_value );
	}

	public function equals( self $other ): bool {
		return $this->value === $other->value;
	}

	public function __toString(): string {
		switch ( $this->value ) {
			case NetworkCategoryIdConstants::MAINNET:
				return 'mainnet';
			case NetworkCategoryIdConstants::TESTNET:
				return 'testnet';
			case NetworkCategoryIdConstants::PRIVATENET:
				return 'privatenet';
			default:
				// ここは通らない
				throw new \InvalidArgumentException( '[D41C6428] Invalid network category ID: ' . $this->value );
		}
	}

	private function checkValue( int $network_category_id_value ): void {
		// NetworkCategoryIdConstantsに定義されている値のみ許容
		$reflection       = new \ReflectionClass( NetworkCategoryIdConstants::class );
		$public_constants = array_values(
			array_map(
				static fn( \ReflectionClassConstant $constant ): int => $constant->getValue(),
				array_filter(
					$reflection->getReflectionConstants(),
					static fn( \ReflectionClassConstant $constant ): bool => $constant->isPublic()
				)
			)
		);
		if ( ! in_array( $network_category_id_value, $public_constants, true ) ) {
			throw new \InvalidArgumentException( '[6AD1DCA2] Invalid network category ID: ' . $network_category_id_value );
		}
	}
}
