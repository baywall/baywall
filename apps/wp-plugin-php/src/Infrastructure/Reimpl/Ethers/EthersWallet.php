<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers;

use Elliptic\EC;

class EthersWallet {

	/** 署名用のキー */
	private EthersSigningKey $signing_key;

	public function __construct(
		#[\SensitiveParameter]
		string $private_key
	) {
		$this->signing_key = new EthersSigningKey( $private_key );
	}

	public static function createRandom(): self {
		$ec          = new EC( 'secp256k1' );
		$key_pair    = $ec->genKeyPair();
		$private_key = str_pad( $key_pair->getPrivate( 'hex' ), 64, '0', STR_PAD_LEFT );

		return new self( "0x{$private_key}" );
	}

	/** ウォレットのアドレスを取得します。 */
	public function address(): string {
		return Ethers::computeAddress( $this->signing_key->publicKey() );
	}

	/** ウォレットの秘密鍵を取得します。 */
	public function privateKey(): string {
		return $this->signing_key->privateKey();
	}

	/**
	 * EIP-191に準拠した署名を行います。
	 *
	 * @param string $message 署名対象メッセージ(EIP-191のプレフィックスは含まない)
	 * @return string 署名
	 *
	 * @see https://ethereum.stackexchange.com/a/86503
	 */
	public function signMessage( string $message ): string {
		return $this->signing_key->sign( Ethers::hashMessage( $message ) );
	}
}
