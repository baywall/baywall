<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

use Cornix\Serendipity\Core\Application\ValueObject\Erc4361NonceString;

class WpErc4361NonceString extends Erc4361NonceString {

	private function __construct( string $nonce_string_value ) {
		parent::__construct( $nonce_string_value );
	}

	public static function generate(): self {
		// https://eips.ethereum.org/EIPS/eip-4361 には、以下のように説明がある。
		// > A random string typically chosen by the relying party and used to prevent replay attacks, at least 8 alphanumeric characters.
		//
		// base64を使う場合、8文字分は6バイト必要。
		// => ここでは8バイトの乱数をbase64エンコードした値をnonceとして使う。

		$random_bytes = random_bytes( 8 );
		$nonce_string = rtrim( base64_encode( $random_bytes ), '=' );
		return new self( $nonce_string );
	}
}
