<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Constants;

use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

final class ChainIdConstants {
	// ==================== Mainnet ====================
	public const ETHEREUM       = 1;    // イーサリアムメインネット(L1)
	public const POLYGON_ZK_EVM = 1101; // Polygon zkEVM(L2/mainnet)

	// ==================== Testnet ====================
	public const SEPOLIA                = 11155111; // イーサリアムSepoliaテストネット(L1)
	public const POLYGON_ZK_EVM_CARDONA = 2442;     // Polygon zkEVMテストネット(L2/Sepolia)

	// ==================== Privatenet ====================
	/** Ethereumの代わりに使用するプライベートネットのチェーンID */
	public const PRIVATENET1 = 31337;
	/** Polygonの代わりに使用するプライベートネットのチェーンID */
	public const PRIVATENET2 = 1337;



	// ==================== Mainnet ====================

	/** イーサリアムメインネット */
	public static function ethereum(): ChainId {
		return ChainId::from( self::ETHEREUM );
	}
	/** Polygon zkEVM メインネット */
	public static function polygonZkEvm(): ChainId {
		return ChainId::from( self::POLYGON_ZK_EVM );
	}

	// ==================== Testnet ====================

	/** イーサリアムSepoliaテストネット */
	public static function sepolia(): ChainId {
		return ChainId::from( self::SEPOLIA );
	}
	/** Polygon zkEVM テストネット */
	public static function polygonZkEvmCardona(): ChainId {
		return ChainId::from( self::POLYGON_ZK_EVM_CARDONA );
	}

	// ==================== Privatenet ====================

	/** Privatenet L1 */
	public static function privatenetL1(): ChainId {
		return ChainId::from( self::PRIVATENET1 );
	}
	/** Privatenet L2 */
	public static function privatenetL2(): ChainId {
		return ChainId::from( self::PRIVATENET2 );
	}

	private function __construct() {} // インスタンス生成禁止
}
