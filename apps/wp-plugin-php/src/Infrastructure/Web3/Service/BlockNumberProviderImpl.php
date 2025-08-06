<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Application\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;

class BlockNumberProviderImpl implements BlockNumberProvider {

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}
	private ChainRepository $chain_repository;

	/** @inheritdoc */
	public function getByChainId( ChainId $chain_id, ?BlockTag $block_tag = null ): BlockNumber {
		$chain = $this->chain_repository->get( $chain_id );
		if ( $chain === null ) {
			throw new \InvalidArgumentException( "[4019A7FD] Chain with ID {$chain_id} does not exist." );
		}
		return $this->getByChain( $chain, $block_tag );
	}

	/** 指定したチェーンからブロック番号を取得します */
	private function getByChain( Chain $chain, ?BlockTag $block_tag = null ): BlockNumber {
		$rpc_url = $chain->rpcUrl();
		if ( $rpc_url === null ) {
			throw new \InvalidArgumentException( "[6DD872FD] Chain {$chain->id()} does not have a valid RPC URL." );
		}
		return $this->getByRpcUrl( $rpc_url, $block_tag );
	}

	/** 指定したRPC URLからブロック番号を取得します */
	private function getByRpcUrl( RpcUrl $rpc_url, ?BlockTag $block_tag = null ): BlockNumber {
		$block_tag = $block_tag ?? BlockTag::latest();
		$client    = ( new BlockchainClient( $rpc_url ) );

		if ( $block_tag->equals( BlockTag::latest() ) ) {
			// ブロック番号取得のみであればeth_blockNumberの方が効率がよい
			return $client->ethBlockNumber();
		} else {
			return $client->ethGetBlockByNumber( $block_tag )->number();
		}
	}
}
