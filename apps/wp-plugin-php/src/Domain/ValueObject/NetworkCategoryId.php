<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * ネットワークカテゴリIDを表すクラス
 */
final class NetworkCategoryId implements ValueObject {

	/** メインネット(Ethereumメインネット、Polygonメインネット等) */
	public const MAINNET = 1;
	/** テストネット(Ethereum Sepolia等) */
	public const TESTNET = 2;
	/** プライベートネット(Ganache、Hardhat等) */
	public const PRIVATENET = 3;

	private const VALID_VALUES = array( self::MAINNET, self::TESTNET, self::PRIVATENET );

	private function __construct( int $network_category_id_value ) {
		$this->checkValue( $network_category_id_value );
		$this->value = $network_category_id_value;
	}

	/** ネットワークカテゴリID(数値) */
	private readonly int $value;

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
		return match ( $this->value ) {
			self::MAINNET    => 'mainnet',
			self::TESTNET    => 'testnet',
			self::PRIVATENET => 'privatenet',
			// default は未知の値に対する防御。通常ここは通らない
			default => throw new \InvalidArgumentException( '[D41C6428] Invalid network category ID: ' . $this->value ),
		};
	}

	private function checkValue( int $network_category_id_value ): void {
		if ( ! in_array( $network_category_id_value, self::VALID_VALUES, true ) ) {
			throw new \InvalidArgumentException( '[6AD1DCA2] Invalid network category ID: ' . $network_category_id_value );
		}
	}
}
