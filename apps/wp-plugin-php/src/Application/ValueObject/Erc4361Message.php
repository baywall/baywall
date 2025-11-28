<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * ERC-4361のメッセージを表すクラス
 *
 * @see https://eips.ethereum.org/EIPS/eip-4361
 */
class Erc4361Message extends StringValueObject {

	private function __construct( string $message_value ) {
		parent::__construct( $message_value );
		$this->checkErc4361Format( $message_value );
	}

	public static function from( string $message_value ): self {
		return new self( $message_value );
	}

	/** ERC-4361のメッセージフォーマットチェック（簡易）を行います。 */
	private function checkErc4361Format( string $message_value ): void {
		// 改行コードにCRが含まれていないこと
		if ( false !== str_contains( $message_value, "\r" ) ) {
			throw new \InvalidArgumentException( "[07AA7AB0] Invalid ERC-4361 message format. {$message_value} contains CR character." );
		}

		$expected_contain_strings = array(
			"wants you to sign in with your Ethereum account:\n",
			"\nURI: ",
			"\nVersion: ",
			"\nChain ID: ",
			"\nNonce: ",
			"\nIssued At: ",
		);
		foreach ( $expected_contain_strings as $expected_string ) {
			if ( false === str_contains( $message_value, $expected_string ) ) {
				throw new \InvalidArgumentException( "[34DF637E] Invalid ERC-4361 message format. Missing expected string: {$expected_string}" );
			}
		}
	}
}
