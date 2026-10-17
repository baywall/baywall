<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\BlockTag;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\EthBlock;
use Baywall\Core\Domain\ValueObject\RpcUrl;

/** ブロックチェーンのブロック・アカウント・ログ情報を取得するインターフェース */
interface BlockchainService {
	public function getBlockByNumber( RpcUrl $rpc_url, BlockNumber|BlockTag $block_number_or_tag ): EthBlock;

	public function getChainId( RpcUrl $rpc_url ): ChainId;

	public function getBlockNumber( RpcUrl $rpc_url ): BlockNumber;

	public function getBalance( RpcUrl $rpc_url, Address $address ): Amount;

	/**
	 * ログを取得します。
	 *
	 * @param array<string, mixed> $filter フィルタ（fromBlock / toBlock / address / topics）
	 * @return \stdClass[] ログの配列
	 */
	public function getLogs( RpcUrl $rpc_url, array $filter ): array;
}
