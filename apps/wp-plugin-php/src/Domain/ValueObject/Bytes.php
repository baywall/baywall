<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * バイトデータを表すクラス
 */
class Bytes implements ValueObject {

	private function __construct( string $bin_value ) {
		$this->bin_value = $bin_value;
	}

	/** バイナリデータとしての値 */
	private string $bin_value;

	/** バイナリデータを取得します */
	public function bin(): string {
		return $this->bin_value;
	}

	public function hex(): Hex {
		return Hex::from( '0x' . bin2hex( $this->bin_value ) );
	}

	public function equals( self $other ): bool {
		return $this->bin_value === $other->bin_value;
	}

	public function __toString(): string {
		return (string) $this->hex();
	}

	/**
	 * 指定したバイト長の値を生成します。
	 */
	public static function generateRandom( int $bytes ): self {
		// `wp_generate_uuid4`は`mt_rand`を用いているため、別の方法で乱数を生成する。
		// 参考:
		// - wp_generate_uuid4: https://developer.wordpress.org/reference/functions/wp_generate_uuid4/
		// - mt_rand: https://www.php.net/manual/ja/function.mt-rand.php
		// 　> この関数が生成する値は、暗号学的にセキュアではありません。そのため、これを暗号や、戻り値を推測できないことが必須の値として使っては いけません。
		// 　> 簡単なユースケースの場合、random_int() と random_bytes() 関数が、オペレーティングシステムの CSPRNG を使った、 便利で安全な API を提供します。

		return new self( random_bytes( $bytes ) );
	}
}
