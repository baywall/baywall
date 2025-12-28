<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers;

use Elliptic\EC;
use Elliptic\EC\KeyPair;

/**
 * 署名用の鍵を表すクラス
 *
 * @see https://docs.ethers.org/v6/api/crypto/#SigningKey
 */
class EthersSigningKey {

	private KeyPair $key_pair;

	public function __construct(
		#[\SensitiveParameter]
		string $private_key
	) {
		assert( preg_match( '/^0x[0-9a-f]{1,64}$/', $private_key ) );
		$this->key_pair = ( new EC( 'secp256k1' ) )->keyFromPrivate( str_replace( '0x', '', $private_key ) );
	}

	public function sign( string $digest ): string {
		assert( preg_match( '/^0x[0-9a-f]{64}$/', $digest ) );

		$signature = $this->key_pair->sign( str_replace( '0x', '', $digest ), array( 'canonical' => true ) );

		$r = str_pad( $signature->r->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$s = str_pad( $signature->s->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$v = dechex( $signature->recoveryParam + 27 );

		$result = "0x{$r}{$s}{$v}";
		assert( preg_match( '/^0x[0-9a-f]{130}$/', $result ) );
		return $result;
	}

	/** 秘密鍵を取得します。 */
	public function privateKey(): string {
		return '0x' . $this->key_pair->getPrivate( 'hex' );
	}

	/** 公開鍵を取得します。 */
	public function publicKey(): string {
		return '0x' . $this->key_pair->getPublic( 'hex' );
	}

	public function __debugInfo() {
		return array(
			// プライベートキーはデバッグ出力から除外
			'private_key' => '*** sensitive data ***',
		);
	}
}
