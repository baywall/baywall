<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Constants;

use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\ChainIdConstants as CHAIN_ID;

/** ネイティブトークンの定義 */
class NativeTokenConstants {
	// 定義を追加する場合、ここから情報を取得すると良い
	// https://chainlistapi.com/chains/1

	public const DEFINITIONS = array(
		CHAIN_ID::ETHEREUM    => array(
			'symbol'   => 'ETH',
			'decimals' => 18,
			'name'     => 'Ether',
		),
		CHAIN_ID::SEPOLIA     => array(
			'symbol'   => 'ETH',
			'decimals' => 18,
			'name'     => 'Ether',
		),
		CHAIN_ID::PRIVATENET1 => array(
			'symbol'   => 'ETH',
			'decimals' => 18,
			'name'     => 'Ether',
		),
		CHAIN_ID::PRIVATENET2 => array(
			'symbol'   => 'POL',
			'decimals' => 18,
			'name'     => 'POL',
		),
	);
}
