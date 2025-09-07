<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Exception\ChainIdMismatchException;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;

class ChainIdChecker {
	/**
	 * RPC URLに接続してチェーンIDを取得し、期待されるチェーンIDと一致するか確認します。
	 * チェーンIDが一致しない場合は例外をスローします。
	 *
	 * @param RpcUrl  $rpc_url
	 * @param ChainId $expected_chain_id
	 * @throws ChainIdMismatchException
	 */
	public function checkChainId( RpcUrl $rpc_url, ChainId $expected_chain_id ): void {
		// RPC URLを使ってブロックチェーンに接続し、チェーンIDを取得
		$actual_chain_id = ( new BlockchainClient( $rpc_url ) )->ethChainId();

		if ( ! $expected_chain_id->equals( $actual_chain_id ) ) {
			throw new ChainIdMismatchException( "[45BC2FC0] Expected: {$expected_chain_id}, Actual: {$actual_chain_id}" );
		}
	}
}
