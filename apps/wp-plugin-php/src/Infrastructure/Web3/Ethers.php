<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Bytes32;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\PrivateKey;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Elliptic\EC;
use kornrunner\Keccak;

// TODO: 引数及び戻り値をプリミティブ型に変更し、Infrastructure/Reimpl/ ディレクトリ以下に移動
class Ethers {

	public static function keccak256( SigningMessage $message ): Bytes32 {
		$keccak256_hex_value = '0x' . self::rawKeccak256( $message->value() );
		return Bytes32::fromHex( Hex::from( $keccak256_hex_value ) );
	}

	private static function rawKeccak256( string $data ): string {
		$result = Keccak::hash( $data, 256 );
		assert( preg_match( '/^[0-9a-f]{64}$/', $result ) );
		return $result;
	}

	/**
	 * EC/KeyPairに変換します
	 *
	 * @disregard P1009 Undefined type
	 */
	private static function signerPrivateKeyToEcKeyPair(
		#[\SensitiveParameter]
		PrivateKey $private_key
	): \Elliptic\EC\KeyPair {
		$ec = new EC( 'secp256k1' );
		return $ec->keyFromPrivate( $private_key->value() );
	}

	/**
	 * メッセージ及び署名からウォレットアドレスを取得します。
	 *
	 * @see https://github.com/simplito/elliptic-php?tab=readme-ov-file#verifying-ethereum-signature
	 */
	public static function verifyMessage( SigningMessage $message, Signature $signature ): ?Address {

		$message_hash    = self::rawKeccak256( self::eip191( $message )->value() );
		$signature_value = $signature->hex()->value();
		$sign            = array(
			'r' => substr( $signature_value, 2, 64 ),
			's' => substr( $signature_value, 66, 64 ),
		);
		$recid           = ord( hex2bin( substr( $signature_value, 130, 2 ) ) ) - 27;
		if ( $recid != ( $recid & 1 ) ) {
			return null;
		}

		$ec = new EC( 'secp256k1' );
		/** @var \Elliptic\Curve\ShortCurve\Point */
		$public_key = $ec->recoverPubKey( $message_hash, $sign, $recid );

		return self::computeAddress( $public_key );
	}


	/**
	 * メッセージをEIP191に準拠した形式に変換します。
	 *
	 * @see https://eips.ethereum.org/EIPS/eip-191
	 */
	public static function eip191( SigningMessage $message ): SigningMessage {
		$message_value  = $message->value();
		$message_length = strlen( $message_value );
		return SigningMessage::from( "\x19Ethereum Signed Message:\n{$message_length}{$message_value}" );
	}

	/**
	 * 秘密鍵からアドレスを取得します
	 *
	 * @disregard P1009 Undefined type
	 */
	public static function privateKeyToAddress(
		#[\SensitiveParameter]
		PrivateKey $private_key
	): Address {
		$key_pair = self::signerPrivateKeyToEcKeyPair( $private_key );
		return self::computeAddress( $key_pair->getPublic() );
	}

	/**
	 * ランダムな秘密鍵を生成します。
	 */
	public static function generatePrivateKey(): PrivateKey {
		$ec       = new EC( 'secp256k1' );
		$key_pair = $ec->genKeyPair();
		return PrivateKey::from( $key_pair->getPrivate( 'hex' ) );
	}

	/**
	 * 公開鍵からウォレットアドレスを取得します。
	 *
	 * @see https://github.com/simplito/elliptic-php#verifying-ethereum-signature
	 */
	private static function computeAddress( \Elliptic\Curve\ShortCurve\Point $public_key ): Address {
		$hash_value    = self::rawKeccak256( substr( hex2bin( $public_key->encode( 'hex' ) ), 1 ) );
		$address_value = \Web3\Utils::toChecksumAddress( substr( $hash_value, 24 ) );
		return Address::from( $address_value );
	}

	/**
	 * メッセージに署名を行います。(EIP191準拠)
	 *
	 * @see https://ethereum.stackexchange.com/a/86503
	 * @disregard P1009 Undefined type
	 */
	public static function signMessage(
		#[\SensitiveParameter]
		PrivateKey $private_key,
		SigningMessage $message
	): Signature {
		$message_hash = self::keccak256( self::eip191( $message ) );

		$key_pair  = self::signerPrivateKeyToEcKeyPair( $private_key );
		$signature = $key_pair->sign( bin2hex( $message_hash->bin() ), array( 'canonical' => true ) );

		$r = str_pad( $signature->r->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$s = str_pad( $signature->s->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$v = dechex( $signature->recoveryParam + 27 );

		$signature = Signature::from( "0x$r$s$v" );

		return $signature;
	}
}
