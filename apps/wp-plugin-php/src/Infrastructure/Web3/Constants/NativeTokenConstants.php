<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Constants;

/** ネイティブトークンの定義 */
final class NativeTokenConstants {
	// 定義を追加する場合、ここから情報を取得すると良い
	// https://chainlistapi.com/chains/1

	public const DEFINITIONS = array(
		ChainIdConstants::ETHEREUM     => array(
			'symbol'   => 'ETH',
			'decimals' => 18,
			'name'     => 'Ether',
		),
		ChainIdConstants::BASE         => array(
			'symbol'   => 'ETH',
			'decimals' => 18,
			'name'     => 'Ether',
		),
		ChainIdConstants::POLYGON_POS  => array(
			'symbol'   => 'POL',
			'decimals' => 18,
			'name'     => 'POL',
		),
		ChainIdConstants::SEPOLIA      => array(
			'symbol'   => 'ETH',
			'decimals' => 18,
			'name'     => 'Ether',
		),
		ChainIdConstants::BASE_SEPOLIA => array(
			'symbol'   => 'ETH',
			'decimals' => 18,
			'name'     => 'Ether',
		),
		ChainIdConstants::POLYGON_AMOY => array(
			'symbol'   => 'POL',
			'decimals' => 18,
			'name'     => 'POL',
		),
		ChainIdConstants::PRIVATENET1  => array(
			'symbol'   => 'ETH',
			'decimals' => 18,
			'name'     => 'Ether',
		),
	);
}
