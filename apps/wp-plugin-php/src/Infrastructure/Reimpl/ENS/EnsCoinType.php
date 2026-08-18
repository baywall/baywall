<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Reimpl\ENS;

/**
 * ENS で使用する coinType への変換を行います。
 *
 * SLIP-44 に基づき、Ethereum Mainnet (chainId=1) は coinType 60、
 * それ以外の EVM チェーンは 0x80000000 + chainId を返します。
 *
 * @see https://github.com/ensdomains/docs/blob/main/docs/ensip/9.md
 */
final class EnsCoinType {

	/** Ethereum Mainnet の SLIP-44 coinType */
	private const ETH_COIN_TYPE = 60;

	/** EVM チェーン用 coinType のオフセット (0x80000000) */
	private const EVM_COIN_TYPE_OFFSET = 0x80000000;

	private function __construct() {}

	/**
	 * チェーンID を ENS の coinType に変換します。
	 *
	 * Ethereum Mainnet (chainId=1) は SLIP-44 の coinType 60 を返します。
	 * それ以外の EVM チェーンは 0x80000000 + chainId を返します。
	 *
	 * @param int $chain_id チェーンID（正の整数）
	 * @return int coinType
	 */
	public static function toCoinType( int $chain_id ): int {
		if ( $chain_id <= 0 ) {
			throw new \InvalidArgumentException( '[558D7BD8] Chain ID must be a positive integer.' );
		}
		if ( 1 === $chain_id ) {
			return self::ETH_COIN_TYPE;
		}
		return self::EVM_COIN_TYPE_OFFSET + $chain_id;
	}
}
