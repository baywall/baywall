<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Security;

use Cornix\Serendipity\Core\Lib\Strings\Strings;

/**
 * 本システムにおいて`check～`は、引数の値を検証し、不正な値の場合は例外をスローする動作を行います。
 * これはスマートコントラクトのライブラリが`check～`関数で`revert`を行っているものを参考にしています。
 *
 * 参考: Ownable.sol#_checkOwner
 * https://github.com/OpenZeppelin/openzeppelin-contracts/blob/1edc2ae004974ebf053f4eba26b45469937b9381/contracts/access/Ownable.sol#L63-L67
 * @deprecated
 */
class Validate {

	/**
	 * 文字列が16進数表記でない場合は例外をスローします。
	 *
	 * @param string $hex
	 * @throws \InvalidArgumentException
	 */
	public static function checkHex( string $hex ): void {
		if ( ! self::isHex( $hex ) ) {
			throw new \InvalidArgumentException( '[95E1280E] Invalid hex. - hex: ' . $hex );
		}
	}
	/**
	 * 文字列が16進数表記かどうかを返します。
	 */
	public static function isHex( string $hex ): bool {
		// 本プラグインでは、`0x`プレフィックス含む文字列を16進数表記とする。
		return Strings::starts_with( $hex, '0x' ) && \Web3\Utils::isHex( $hex );
	}

	/**
	 * 文字列がURLの形式かどうかを返します。
	 */
	public static function isUrl( string $url ): bool {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false && Strings::starts_with( $url, 'http' );
	}

	/** 指定した文字列がブロックのタグ名であるかどうかを判定します。 */
	public static function isBlockTagName( string $block_tag ): bool {
		// 参考: https://www.alchemy.com/overviews/ethereum-commitment-levels
		return in_array( $block_tag, array( 'latest', 'safe', 'finalized' ), true );
	}
	/** 指定した文字列がブロックのタグ名であることをチェックし、不正な文字列の場合は例外をスローします。 */
	public static function checkBlockTagName( string $block_tag ): void {
		if ( ! self::isBlockTagName( $block_tag ) ) {
			throw new \InvalidArgumentException( '[5B634FE3] Invalid tag. tag: ' . $block_tag );
		}
	}


	/** 指定した文字列が請求書に紐づくnonce値のフォーマットであるかどうかを判定します。 */
	public static function isInvoiceNonceValueFormat( string $invoice_nonce_value ): bool {
		// 請求書に紐づくnonceは、128bitのHEX(`0x`プレフィックス無し)文字列
		return preg_match( '/^[0-9a-f]{32}$/i', $invoice_nonce_value ) === 1;
	}

	/** 指定した文字列が請求書に紐づくnonce値のフォーマットでない場合は例外をスローします。 */
	public static function checkInvoiceNonceValueFormat( string $invoice_nonce_value ): void {
		if ( ! self::isInvoiceNonceValueFormat( $invoice_nonce_value ) ) {
			throw new \InvalidArgumentException( '[8EEF9FD6] Invalid invoice nonce value format. - value: ' . $invoice_nonce_value );
		}
	}
}
