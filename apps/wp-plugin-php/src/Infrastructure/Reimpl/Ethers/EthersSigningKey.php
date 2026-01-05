<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers;

use Elliptic\EC;
use Elliptic\EC\KeyPair;
use InvalidArgumentException;

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
		if ( ! preg_match( '/^0x[0-9a-f]{64}$/', $private_key ) ) {
			throw new InvalidArgumentException( "[6F4D3BF3] Invalid private key. {$private_key}" );
		}
		$this->key_pair = ( new EC( 'secp256k1' ) )->keyFromPrivate( str_replace( '0x', '', $private_key ) );
	}

	public function sign( string $digest ): string {
		if ( ! preg_match( '/^0x[0-9a-f]{64}$/', $digest ) ) {
			throw new InvalidArgumentException( "[74669166] Invalid digest. {$digest}" );
		}

		$signature = $this->key_pair->sign( str_replace( '0x', '', $digest ), array( 'canonical' => true ) );

		$r = str_pad( $signature->r->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$s = str_pad( $signature->s->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$v = dechex( $signature->recoveryParam + 27 );

		$result = "0x{$r}{$s}{$v}";
		assert( preg_match( '/^0x[0-9a-f]{130}$/', $result ) );
		return $result;
	}

	/**
	 * 秘密鍵を取得します。
	 *
	 * @return string 32バイトの16進数文字列('0x'+64文字)
	 *
	 * - ethers.jsの秘密鍵生成時、先頭バイトが0であっても`0x`+64文字で出力
	 * - MetaMaskの秘密鍵エクスポートは先頭バイトが0であっても64文字で出力
	 */
	public function privateKey(): string {
		return '0x' . str_pad( $this->key_pair->getPrivate( 'hex' ), 64, '0', STR_PAD_LEFT );
	}

	/**
	 * 公開鍵を取得します。
	 *
	 * - ethers.jsでは非圧縮形式の公開鍵を用いているため、それに合わせて`$compact`に`false`を指定
	 *   (`Wallet.createRandom().signingKey.publicKey` が `0x04` で開始する値を返す)
	 */
	public function publicKey(): string {
		$public_key = '0x' . $this->key_pair->getPublic( false, 'hex' );
		assert( preg_match( '/^0x04[0-9a-f]{128}/', $public_key ) );
		return $public_key;
	}

	public function __debugInfo() {
		return array(
			// プライベートキーはデバッグ出力から除外
			'private_key' => '*** sensitive data ***',
		);
	}
}
