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
	 * @param string $rpc_url_value
	 * @param int    $expected_chain_id_value
	 * @throws ChainIdMismatchException
	 */
	public function checkChainId( string $rpc_url_value, int $expected_chain_id_value ): void {
		$rpc_url  = RpcUrl::from( $rpc_url_value );
		$chain_id = ChainId::from( $expected_chain_id_value );

		// RPC URLを使ってブロックチェーンに接続し、チェーンIDを取得
		$actual_chain_id = ( new BlockchainClient( $rpc_url ) )->ethChainId();

		if ( ! $chain_id->equals( $actual_chain_id ) ) {
			throw new ChainIdMismatchException( "[45BC2FC0] Expected: {$chain_id}, Actual: {$actual_chain_id}" );
		}
	}
}
