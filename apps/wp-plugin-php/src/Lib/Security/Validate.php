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
 *
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
}
