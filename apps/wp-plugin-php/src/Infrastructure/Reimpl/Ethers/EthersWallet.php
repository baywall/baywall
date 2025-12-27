<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers;

use Elliptic\EC;

class EthersWallet {

	/** 秘密鍵。内部では`0x`無しの16進数文字列として保持。 */
	private string $private_key;

	public function __construct(
		#[\SensitiveParameter]
		string $private_key
	) {
		assert( preg_match( '/^0x[0-9a-f]{1,64}$/', $private_key ) );
		$this->private_key = str_replace( '0x', '', $private_key );
	}

	public static function createRandom(): self {
		$ec          = new EC( 'secp256k1' );
		$key_pair    = $ec->genKeyPair();
		$private_key = $key_pair->getPrivate( 'hex' );

		return new self( "0x{$private_key}" );
	}

	private function keyPair(): \Elliptic\EC\KeyPair {
		$ec = new EC( 'secp256k1' );
		return $ec->keyFromPrivate( $this->private_key );
	}

	/** ウォレットのアドレスを取得します。 */
	public function address(): string {
		return Ethers::computeAddress( $this->keyPair()->getPublic() );
	}

	/** ウォレットの秘密鍵を取得します。 */
	public function privateKey(): string {
		return '0x' . $this->private_key;
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
		$hash      = str_replace( '0x', '', Ethers::hashMessage( $message ) );
		$signature = $this->keyPair()->sign( $hash, array( 'canonical' => true ) );

		$r = str_pad( $signature->r->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$s = str_pad( $signature->s->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$v = dechex( $signature->recoveryParam + 27 );

		return "0x{$r}{$s}{$v}";
	}

	public function __debugInfo() {
		return array(
			// プライベートキーはデバッグ出力から除外
			'private_key' => '*** sensitive data***',
		);
	}
}
