<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Constants;

final class NetworkCategoryIdConstants {

	/** メインネット(Ethereumメインネット、Polygonメインネット等) */
	public const MAINNET = 1;
	/** テストネット(Ethereum Sepolia等) */
	public const TESTNET = 2;
	/** プライベートネット(Ganache、Hardhat等) */
	public const PRIVATENET = 3;

	private function __construct() {} // インスタンス生成禁止
}
