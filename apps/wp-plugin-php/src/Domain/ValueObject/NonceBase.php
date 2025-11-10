<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * nonceを表す基底クラス
 */
abstract class NonceBase implements \Stringable {

	protected function __construct( string $nonce_value ) {
		if ( ! $this->isNonceValueFormat( $nonce_value ) ) {
			throw new \InvalidArgumentException( "[6A2C68E6] Invalid invoice nonce value format: {$nonce_value}" );
		}
		$this->nonce_value = $nonce_value;
	}

	private string $nonce_value;

	/** nonceの文字列を取得します */
	public function value(): string {
		return $this->nonce_value;
	}

	public function __toString(): string {
		return $this->nonce_value;
	}

	/** Nonceのフォーマットが正しいかどうかを返します */
	abstract protected function isNonceValueFormat( string $nonce_value ): bool;

	/**
	 * 指定したバイト長のnonce値を生成します。
	 */
	protected static function generateNonceValue( int $byte ): string {
		return bin2hex( Bytes::generateRandom( $byte )->bin() );
	}
}
