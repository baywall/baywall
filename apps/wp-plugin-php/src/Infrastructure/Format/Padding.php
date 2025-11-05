<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Format;

class Padding {
	/**
	 * 16進数の値を32バイトの16進数に変換します。
	 * 用途: イベントのtopics等
	 */
	public function toBytes32Hex( string $hex ): string {
		assert( strlen( $hex ) <= 66, '[71E5FB97]' ); // 0x + 32バイトの16進数のため、66文字まで
		$hex = strtolower( $hex ); // アドレスが渡される可能性があるため、数値として扱うために小文字にする
		if ( ! preg_match( '/^0x[0-9a-f]{1,64}$/', $hex ) ) {
			throw new \InvalidArgumentException( '[53388310] Invalid hex format. - hex: ' . $hex );
		}
		$hex = str_replace( '0x', '', $hex );
		$hex = str_pad( $hex, 64, '0', STR_PAD_LEFT );
		return strtolower( '0x' . $hex ); // 32バイトの16進数のため、すべて小文字にして返す
	}
}
