<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Constants;

use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;

final class NetworkCategoryIdConstants {

	private const MAINNET    = 1;
	private const TESTNET    = 2;
	private const PRIVATENET = 3;

	public static function all(): array {
		$reflection = new \ReflectionClass( __CLASS__ );
		$constants  = $reflection->getConstants();

		return array_map(
			fn( $constant_value ) => NetworkCategoryId::from( $constant_value ),
			$constants
		);
	}

	/** メインネット */
	public static function mainnet(): NetworkCategoryId {
		return NetworkCategoryId::from( self::MAINNET );
	}

	/** テストネット */
	public static function testnet(): NetworkCategoryId {
		return NetworkCategoryId::from( self::TESTNET );
	}

	/** プライベートネット */
	public static function privatenet(): NetworkCategoryId {
		return NetworkCategoryId::from( self::PRIVATENET );
	}

	private function __construct() {} // インスタンス生成禁止
}
