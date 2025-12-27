<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers;

use Elliptic\EC;
use kornrunner\Keccak;

class Ethers {
	/** EIP191のメッセージプレフィックス */
	public const MESSAGE_PREFIX = "\x19Ethereum Signed Message:\n";

	/**
	 * Keccak-256ハッシュを計算します。
	 *
	 * @param string $hex_value HEX文字列（`0x`で始まる）
	 * @return string
	 */
	public static function keccak256( string $hex_value ): string {
		assert( preg_match( '/^0x[0-9a-f]*$/', $hex_value ) );
		$result = '0x' . Keccak::hash( hex2bin( substr( $hex_value, 2 ) ), 256 );
		assert( preg_match( '/^0x[0-9a-f]{64}$/', $result ) );
		return $result;
	}

	/**
	 * メッセージハッシュ(EIP191)を計算します。
	 *
	 * @param string $message 元のメッセージ
	 * @return string メッセージハッシュ(EIP191)
	 *
	 * @see https://eips.ethereum.org/EIPS/eip-191
	 */
	public static function hashMessage( string $message ): string {
		// プレフィックスが既に含まれている場合はエラー
		if ( false !== strpos( $message, self::MESSAGE_PREFIX ) ) {
			throw new \InvalidArgumentException( '[4A16EBEE] The message is already EIP191 formatted message.' );
		}

		// EIP191形式に変換したメッセージのKeccak-256ハッシュを計算
		$result = self::keccak256( '0x' . bin2hex( self::MESSAGE_PREFIX . strlen( $message ) . $message ) );
		assert( preg_match( '/^0x[0-9a-f]{64}$/', $result ) );
		return $result;
	}

	/**
	 * メッセージと署名からウォレットアドレスを復元します。
	 *
	 * @param string $message 元のメッセージ
	 * @param string $signature 署名（`0x`で始まる130文字のHEX文字列）
	 * @return string|null 復元したウォレットアドレス（`0x`で始まる40文字のHEX文字列）、復元に失敗した場合は`null`
	 *
	 * @see https://github.com/simplito/elliptic-php?tab=readme-ov-file#verifying-ethereum-signature
	 */
	public static function verifyMessage( string $message, string $signature ): ?string {
		assert( preg_match( '/^0x[0-9a-f]{130}$/', $signature ) );

		$hash  = str_replace( '0x', '', self::hashMessage( $message ) );
		$sign  = array(
			'r' => substr( $signature, 2, 64 ),
			's' => substr( $signature, 66, 64 ),
		);
		$recid = ord( hex2bin( substr( $signature, 130, 2 ) ) ) - 27;
		if ( $recid != ( $recid & 1 ) ) {
			return null;
		}

		$ec = new EC( 'secp256k1' );
		/** @var \Elliptic\Curve\ShortCurve\Point */
		$public_key = $ec->recoverPubKey( $hash, $sign, $recid );

		$result = self::computeAddress( $public_key );
		assert( preg_match( '/^0x[0-9a-fA-F]{40}$/', $result ) );
		return $result;
	}

	/**
	 * 公開鍵からウォレットアドレスを取得します。
	 *
	 * @see https://github.com/simplito/elliptic-php#verifying-ethereum-signature
	 */
	public static function computeAddress( \Elliptic\Curve\ShortCurve\Point $public_key ): string {
		$result = '0x' . substr( Keccak::hash( substr( hex2bin( $public_key->encode( 'hex' ) ), 1 ), 256 ), 24 );
		$result = \Web3\Utils::toChecksumAddress( $result ); // チェックサム付きアドレスに変換
		assert( preg_match( '/^0x[0-9a-fA-F]{40}$/', $result ) );
		return $result;
	}


	/**
	 * @param string[]       $types
	 * @param (int|string)[] $values 数値または`0x`始まりのHEX文字列
	 * @return string 連結されたHEX文字列 (`0x`始まり)
	 */
	public static function solidityPacked( array $types, array $values ): string {
		// 引数の長さが一致することを確認
		assert( count( $types ) === count( $values ), '[5C6F2D1E] types len: ' . count( $types ) . ', values len: ' . count( $values ) );

		// TODO: pack等で最適化
		$packed = '0x';
		foreach ( $types as $index => $type ) {
			$value = $values[ $index ];
			// valueを一旦`0x`無しのHEXに変換
			if ( is_int( $value ) ) {
				$raw_hex = dechex( $value );
			} elseif ( is_string( $value ) ) {
				assert( preg_match( '/^0x[0-9a-fA-F]+$/', $value ) );
				$raw_hex = strtolower( substr( $value, 2 ) );
			} else {
				throw new \InvalidArgumentException( '[5963C820] Unsupported value type: ' . gettype( $value ) );
			}
			assert( is_string( $raw_hex ), '[4851DA47]' );
			assert( preg_match( '/^[0-9a-f]*$/', $raw_hex ), "[D3C2E2B1] {$raw_hex}" );

			switch ( $type ) {
				case 'address':
					assert( preg_match( '/^[0-9a-f]{40}$/', $raw_hex ), "[EB3A3707] {$raw_hex}" );
					$packed .= $raw_hex;
					break;
				case 'uint256':
				case 'uint128':
				case 'uint64':
					$hex_len      = (int) str_replace( 'uint', '', $type ) / 4;
					$value_packed = str_pad( $raw_hex, $hex_len, '0', STR_PAD_LEFT );
					assert( preg_match( "/^[0-9a-f]{{$hex_len}}$/", $value_packed ), "[7C2D1F6E] {$value_packed}" );
					$packed .= $value_packed;
					break;
				case 'bytes32':
					assert( preg_match( '/^[0-9a-f]{64}$/', $raw_hex ), "[70601E35] {$raw_hex}" );
					$packed .= $raw_hex;
					break;
				case 'bytes':
					$packed .= $raw_hex;
					break;
				default:
					throw new \InvalidArgumentException( "[78263401] Unsupported type: {$type}" );
			}
		}
		return $packed;
	}

	/**
	 * UTF-8の文字列を受け取り、32バイトのIDを返す単純なハッシュ関数
	 *
	 * @param string $value UTF-8の文字列
	 * @return string 32バイトのID
	 * @see https://docs.ethers.org/v6/api/hashing/#id
	 * @example
	 * id( 'hello world' ); // '0x47173285a8d7341e5e972fc677286384f802f8ef42a5ec5f03bbfa254cb01fad'
	 */
	public static function id( string $value ): string {
		return self::keccak256( '0x' . bin2hex( $value ) );
	}
}
