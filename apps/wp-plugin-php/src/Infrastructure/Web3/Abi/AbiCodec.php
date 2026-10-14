<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Abi;

use Baywall\Core\Infrastructure\Format\Padding;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Bytes32;
use InvalidArgumentException;
use kornrunner\Keccak;

/**
 * コントラクト呼び出し（eth_call）用の最小 ABI コーデック。
 *
 * 対応型は本プロダクトで使用する9種（address / bytes32 / bytes / string / uintN / int256 / uint8 / uint80 / bool）に限る。
 * エンコード対応型は入力に現れる address / bytes32 / bytes / string / uintN / bool のみ（int256 等はデコード専用）。
 * 出力は位置配列（list<mixed>）。uintN は10進文字列、int256 は2の補数デコードした10進文字列。
 */
class AbiCodec {

	/** エンコード対応の静的型 */
	private const ENCODABLE_STATIC_TYPES = array( 'address', 'bytes32', 'uint8', 'uint80', 'uint256', 'bool' );

	/**
	 * 関数呼び出しの calldata（0x + 4バイトセレクタ + 引数）をエンコードします。
	 *
	 * @param array<int,mixed> $abi    ABI定義配列
	 * @param string           $method 関数名
	 * @param array<int,mixed> $args   引数（Address / Bytes32 / string / int / numeric-string 等）
	 */
	public function encodeCall( array $abi, string $method, array $args ): string {
		$func = $this->findFunction( $abi, $method );

		$input_types = array_map( fn( $item ) => (string) $item['type'], $func['inputs'] );
		if ( count( $input_types ) !== count( $args ) ) {
			throw new InvalidArgumentException(
				sprintf( '[C3A1B048] Argument count mismatch for %s. expected: %d, actual: %d.', $method, count( $input_types ), count( $args ) )
			);
		}

		$selector = '0x' . substr( Keccak::hash( $method . '(' . implode( ',', $input_types ) . ')', 256 ), 0, 8 );

		return strtolower( $selector . $this->encodeArguments( $input_types, $args, $method ) );
	}

	/**
	 * 関数の戻り値をデコードします。
	 *
	 * @param array<int,mixed> $abi ABI定義配列
	 * @param string           $method 関数名
	 * @param string           $hex 応答の16進文字列（0x + 64hex × n）
	 * @return list<mixed> 位置配列。uintN は10進文字列、int256 は10進文字列（負値含む）、bool は bool、address は小文字0x40hex、bytes32 は0x+64hex、string はUTF-8文字列
	 */
	public function decodeResult( array $abi, string $method, string $hex ): array {
		$func = $this->findFunction( $abi, $method );

		$output_types = array_map( fn( $item ) => (string) $item['type'], $func['outputs'] );
		$data         = strtolower( $this->normalizeHex( $hex, "[D7B2E19A] Empty result for {$method}." ) );
		if ( strlen( $data ) % 64 !== 0 ) {
			throw new InvalidArgumentException(
				sprintf( '[E8C3F2B1] Invalid data length for %s. hex length: %d.', $method, strlen( $data ) )
			);
		}

		$head  = $data;
		$tail  = '';
		$words = array();
		if ( in_array( 'string', $output_types, true ) ) {
			// 動的型を含む場合はワード列を head と tail に分離する
			$word_count = count( $output_types );
			$head       = substr( $data, 0, $word_count * 64 );
			$tail       = substr( $data, $word_count * 64 );
		}
		for ( $i = 0; $i < strlen( $head ); $i += 64 ) {
			$words[] = substr( $head, $i, 64 );
		}
		if ( count( $words ) !== count( $output_types ) ) {
			throw new InvalidArgumentException(
				sprintf( '[F9D4A3C2] Word count mismatch for %s. expected: %d, actual: %d.', $method, count( $output_types ), count( $words ) )
			);
		}

		$results = array();
		foreach ( $output_types as $index => $type ) {
			$results[] = $this->decodeWord( $type, $words[ $index ], $tail, count( $output_types ), $method );
		}

		return $results;
	}

	/**
	 * ABI から対象の function 要素を1件取得します。
	 *
	 * @param array<int,mixed> $abi
	 * @return array<string,mixed>
	 */
	private function findFunction( array $abi, string $method ): array {
		$targets = array_values(
			array_filter( $abi, fn( $item ) => ( $item['type'] ?? '' ) === 'function' && ( $item['name'] ?? '' ) === $method )
		);
		if ( count( $targets ) !== 1 ) {
			throw new InvalidArgumentException( "[A1D9E8F7] Function not found in ABI: {$method}." );
		}

		return $targets[0];
	}

	/**
	 * 引数を ABI エンコードします（head/tail 方式。動的型は string / bytes に対応）。
	 *
	 * @param string[]         $input_types
	 * @param array<int,mixed> $args
	 */
	private function encodeArguments( array $input_types, array $args, string $method ): string {
		$head_parts = array();
		$tail_parts = array();
		// tail 開始位置（バイト）。head は全引数ワード（静的 + 動的オフセット置き場）で構成されるため引数総数 × 32バイト
		$tail_offset = count( $input_types ) * 32;
		foreach ( $args as $index => $arg ) {
			$type = $input_types[ $index ];
			if ( 'string' === $type ) {
				$arg          = $this->checkString( $arg, $method, $index );
				$bytes_hex    = bin2hex( $arg );
				$word_count   = (int) ceil( strlen( $bytes_hex ) / 64 );
				$head_parts[] = str_pad( dechex( $tail_offset ), 64, '0', STR_PAD_LEFT );
				$tail_parts[] = str_pad( dechex( strlen( $arg ) ), 64, '0', STR_PAD_LEFT );
				$tail_parts[] = str_pad( $bytes_hex, $word_count * 64, '0', STR_PAD_RIGHT );
				// 以降の動的データは 長さワード(32バイト) + データワード で積まれる
				$tail_offset += 32 + $word_count * 32;
				continue;
			}
			if ( 'bytes' === $type ) {
				$hex          = $this->checkBytesHex( $arg, $method, $index );
				$data_hex     = substr( $hex, 2 );
				$word_count   = (int) ceil( strlen( $data_hex ) / 64 );
				$head_parts[] = str_pad( dechex( $tail_offset ), 64, '0', STR_PAD_LEFT );
				$tail_parts[] = str_pad( dechex( strlen( $data_hex ) / 2 ), 64, '0', STR_PAD_LEFT );
				$tail_parts[] = str_pad( $data_hex, $word_count * 64, '0', STR_PAD_RIGHT );
				// 以降の動的データは 長さワード(32バイト) + データワード で積まれる
				$tail_offset += 32 + $word_count * 32;
				continue;
			}
			$head_parts[] = $this->encodeStaticWord( $type, $arg, $method, $index );
		}

		return implode( '', $head_parts ) . implode( '', $tail_parts );
	}

	/**
	 * 静的型1件を32バイトワードへエンコードします。
	 */
	private function encodeStaticWord( string $type, mixed $arg, string $method, int $index ): string {
		switch ( true ) {
			case 'address' === $type:
				if ( ! $arg instanceof Address ) {
					throw new InvalidArgumentException( sprintf( '[B2E0C7D9] Argument %d of %s must be Address.', $index, $method ) );
				}
				return substr( ( new Padding() )->toBytes32Hex( $arg->value() ), 2 );

			case 'bytes32' === $type:
				$hex = null;
				if ( $arg instanceof Bytes32 ) {
					$hex = $arg->hex()->value();
				} elseif ( is_string( $arg ) ) {
					$hex = $arg;
				}
				if ( null === $hex || ! preg_match( '/^0x[0-9a-fA-F]{64}$/', $hex ) ) {
					throw new InvalidArgumentException( sprintf( '[C3F1D8E0] Argument %d of %s must be bytes32 hex.', $index, $method ) );
				}
				return strtolower( substr( $hex, 2 ) );

			case 'bool' === $type:
				if ( ! is_bool( $arg ) ) {
					throw new InvalidArgumentException( sprintf( '[D4A2E9F1] Argument %d of %s must be bool.', $index, $method ) );
				}
				return $arg ? str_repeat( '0', 63 ) . '1' : str_repeat( '0', 64 );

			case preg_match( '/^uint\d+$/', $type ) === 1:
				if ( is_int( $arg ) ) {
					if ( $arg < 0 ) {
						// 負値は decToHex で全ゼロワード化するため、ここで例外拒否する
						throw new InvalidArgumentException( sprintf( '[20E5088E] Argument %d of %s must not be negative for uint.', $index, $method ) );
					}
					$dec = (string) $arg;
				} elseif ( is_string( $arg ) && preg_match( '/^\d+$/', $arg ) ) {
					$dec = $arg;
				} else {
					throw new InvalidArgumentException( sprintf( '[E5B3F0A2] Argument %d of %s must be uint.', $index, $method ) );
				}
				$hex = $this->decToHex( $dec );
				if ( strlen( $hex ) > 64 ) {
					throw new InvalidArgumentException( sprintf( '[F6C4A1B3] Argument %d of %s exceeds uint range.', $index, $method ) );
				}
				return str_pad( $hex, 64, '0', STR_PAD_LEFT );

			default:
				// int256 等の入力は本プロダクトの呼び出し箇所に存在しないため未対応
				throw new InvalidArgumentException( sprintf( '[A7D5B2C4] Unsupported input type for %s: %s.', $method, $type ) );
		}
	}

	/**
	 * 1ワードを型に応じてデコードします。
	 */
	private function decodeWord( string $type, string $word, string $tail, int $head_word_count, string $method ): mixed {
		switch ( true ) {
			case 'bool' === $type:
				if ( str_repeat( '0', 64 ) === $word ) {
					return false;
				}
				if ( str_repeat( '0', 63 ) . '1' === $word ) {
					return true;
				}
				throw new InvalidArgumentException( sprintf( '[B8E6C3D5] Invalid bool value for %s: %s.', $method, $word ) );

			case preg_match( '/^uint\d+$/', $type ) === 1:
				return $this->hexToDec( $word );

			case 'int256' === $type:
				// 2の補数デコード（先頭ビットが立っていれば負値。先頭ニブルが8以上）
				if ( hexdec( $word[0] ) >= 8 ) {
					$inverted = '';
					foreach ( str_split( $word ) as $ch ) {
						$inverted .= dechex( 15 - hexdec( $ch ) );
					}
					return '-' . bcadd( $this->hexToDec( $inverted ), '1' );
				}
				return $this->hexToDec( $word );

			case 'address' === $type:
				return '0x' . substr( $word, 24, 40 );

			case 'bytes32' === $type:
				return '0x' . $word;

			case 'string' === $type:
				return $this->decodeDynamicString( $word, $tail, $head_word_count, $method );

			default:
				throw new InvalidArgumentException( sprintf( '[C9F7D4E6] Unsupported output type for %s: %s.', $method, $type ) );
		}
	}

	/**
	 * 動的 string を tail から読み取ります。
	 *
	 * ABI 仕様上、オフセットは「データ部（head + tail 全体）の先頭」からのバイト数のため、
	 * tail 内では head ワード分（32バイト × head ワード数）を差し引いた位置を読む。
	 * tail は16進文字列のため、バイト数は文字数の2倍に拡張して扱う。
	 */
	private function decodeDynamicString( string $offset_word, string $tail, int $head_word_count, string $method ): string {
		$offset = (int) $this->hexToDec( $offset_word );
		// head ワード分のバイト数を差し引き、16進文字列用に2倍する
		$char_offset = ( $offset - $head_word_count * 32 ) * 2;
		if ( $char_offset < 0 || $char_offset + 64 > strlen( $tail ) ) {
			throw new InvalidArgumentException( sprintf( '[D0A8E5F7] Invalid string offset for %s: %d.', $method, $offset ) );
		}
		$length_word = substr( $tail, $char_offset, 64 );
		$length      = (int) $this->hexToDec( $length_word );
		if ( $char_offset + 64 + $length * 2 > strlen( $tail ) ) {
			throw new InvalidArgumentException( sprintf( '[F2C0A7B9] String data out of range for %s.', $method ) );
		}
		$data    = substr( $tail, $char_offset + 64, $length * 2 );
		$decoded = hex2bin( $data );
		if ( false === $decoded ) {
			throw new InvalidArgumentException( sprintf( '[A3D1B8C0] Invalid string hex for %s.', $method ) );
		}

		return $decoded;
	}

	/**
	 * 10進文字列を小文字16進（0x無し）へ変換します（bcmath）。
	 */
	private function decToHex( string $dec ): string {
		$hex = '';
		while ( bccomp( $dec, '0' ) > 0 ) {
			$hex = dechex( (int) bcmod( $dec, '16' ) ) . $hex;
			$dec = bcdiv( $dec, '16', 0 );
		}

		return $hex;
	}

	/**
	 * 小文字16進（0x無し）を10進文字列へ変換します（bcmath）。
	 */
	private function hexToDec( string $hex ): string {
		$result = '0';
		$len    = strlen( $hex );
		for ( $i = 0; $i < $len; $i++ ) {
			$digit  = strpos( '0123456789abcdef', $hex[ $i ] );
			$power  = bcpow( '16', (string) ( $len - $i - 1 ) );
			$result = bcadd( $result, bcmul( (string) $digit, $power ) );
		}

		return $result;
	}

	/**
	 * 応答16進を 0x 無しの小文字へ正規化します。
	 */
	private function normalizeHex( string $hex, string $empty_error ): string {
		$normalized = strtolower( $hex );
		if ( '0x' === $normalized || '0x0' === $normalized ) {
			throw new InvalidArgumentException( $empty_error );
		}
		if ( ! preg_match( '/^0x[0-9a-f]+$/', $normalized ) ) {
			throw new InvalidArgumentException( '[B4E2C9D1] Invalid hex result: ' . $hex );
		}

		return substr( $normalized, 2 );
	}

	/**
	 * string 引数を検証します。
	 */
	private function checkString( mixed $arg, string $method, int $index ): string {
		if ( ! is_string( $arg ) ) {
			throw new InvalidArgumentException( sprintf( '[C5F3D0E2] Argument %d of %s must be string.', $index, $method ) );
		}

		return $arg;
	}

	/**
	 * bytes 型の値を検証し、0x 付き16進文字列として返します。
	 *
	 * 受理形式は Hex と同一（`0x` プレフィックス + 小文字16進・偶数長）。
	 */
	private function checkBytesHex( mixed $arg, string $method, int $index ): string {
		if ( ! is_string( $arg ) || ! preg_match( '/^0x[0-9a-f]+$/', $arg ) || strlen( $arg ) % 2 !== 0 ) {
			throw new InvalidArgumentException( sprintf( '[02AE251A] Argument %d of %s must be bytes hex.', $index, $method ) );
		}

		return $arg;
	}
}
