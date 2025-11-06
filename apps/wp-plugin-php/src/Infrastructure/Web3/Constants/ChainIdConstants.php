<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Constants;

final class ChainIdConstants {
	// ==================== Mainnet ====================
	public const ETHEREUM = 1;    // イーサリアムメインネット(L1)
	// public const POLYGON_ZK_EVM = 1101; // Polygon zkEVM(L2/mainnet)

	// ==================== Testnet ====================
	public const SEPOLIA = 11155111; // イーサリアムSepoliaテストネット(L1)
	// public const POLYGON_ZK_EVM_CARDONA = 2442;     // Polygon zkEVMテストネット(L2/Sepolia)

	// ==================== Privatenet ====================
	/** Ethereumの代わりに使用するプライベートネットのチェーンID */
	public const PRIVATENET1 = 31337;
	/** Polygonの代わりに使用するプライベートネットのチェーンID */
	public const PRIVATENET2 = 1337;
}
