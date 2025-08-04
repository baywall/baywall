<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Registry;

use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

final class ChainIdRegistry {
	// ==================== Mainnet ====================
	private const ETH_MAINNET    = 1;    // イーサリアムメインネット(L1)
	private const POLYGON_ZK_EVM = 1101; // Polygon zkEVM(L2/mainnet)

	// ==================== Testnet ====================
	private const SEPOLIA                = 11155111; // イーサリアムSepoliaテストネット(L1)
	private const POLYGON_ZK_EVM_CARDONA = 2442;     // Polygon zkEVMテストネット(L2/Sepolia)
	private const SONEIUM_MINATO         = 1946;     // Soneiumテストネット(L2/Sepolia)

	// ==================== Privatenet ====================
	private const PRIVATENET_L1 = 31337; // PrivatenetL1に位置付けられたチェーンID
	private const PRIVATENET_L2 = 1337;  // PrivatenetL2に位置付けられたチェーンID(L2) ※実際はロールアップを行っていない、単に独立したネットワーク



	// ==================== Mainnet ====================

	/** イーサリアムメインネット */
	public static function ethMainnet(): ChainID {
		return ChainID::from( self::ETH_MAINNET );
	}
	/** Polygon zkEVM メインネット */
	public static function polygonZkEvm(): ChainID {
		return ChainID::from( self::POLYGON_ZK_EVM );
	}

	// ==================== Testnet ====================

	/** イーサリアムSepoliaテストネット */
	public static function sepolia(): ChainID {
		return ChainID::from( self::SEPOLIA );
	}
	/** Polygon zkEVM テストネット */
	public static function polygonZkEvmCardona(): ChainID {
		return ChainID::from( self::POLYGON_ZK_EVM_CARDONA );
	}
	/** Soneium テストネット */
	public static function soneiumMinato(): ChainID {
		return ChainID::from( self::SONEIUM_MINATO );
	}

	// ==================== Privatenet ====================

	/** Privatenet L1 */
	public static function privatenetL1(): ChainID {
		return ChainID::from( self::PRIVATENET_L1 );
	}
	/** Privatenet L2 */
	public static function privatenetL2(): ChainID {
		return ChainID::from( self::PRIVATENET_L2 );
	}

	private function __construct() {} // インスタンス生成禁止
}
