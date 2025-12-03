<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

use Cornix\Serendipity\Core\Application\ValueObject\Erc4361NonceString;
use Tuupola\Base62;

class WpErc4361NonceString extends Erc4361NonceString {

	private function __construct( string $nonce_string_value ) {
		parent::__construct( $nonce_string_value );
	}

	public static function generate(): self {
		// https://eips.ethereum.org/EIPS/eip-4361 には、以下のように説明がある。
		// > A random string typically chosen by the relying party and used to prevent replay attacks, at least 8 alphanumeric characters.
		//
		// 記号が含まれる時、MetaMaskでうまく解釈されない状況になったため、base64ではなくbase62を使用する。
		// ※ 記号が含まれる場合、MetaMask上でEIP4361用の表示画面でなく通常の署名画面になるだけなので、署名自体は可能。

		return new self( ( new Base62() )->encode( random_bytes( 8 ) ) );
	}
}
