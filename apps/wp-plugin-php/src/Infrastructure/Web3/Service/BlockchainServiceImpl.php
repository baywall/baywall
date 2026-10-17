<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\Service\BlockchainService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\BlockTag;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\EthBlock;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Client\BlockchainClient;
use Baywall\Core\Infrastructure\Web3\Client\RpcTransport;

class BlockchainServiceImpl implements BlockchainService {

	/** テストでトランスポートを差し替えられるようにする（省略時は `HttpRpcTransport`） */
	public function __construct( private readonly ?RpcTransport $rpc_transport = null ) {}

	/** @inheritdoc */
	public function getBlockByNumber( RpcUrl $rpc_url, BlockNumber|BlockTag $block_number_or_tag ): EthBlock {
		return $this->client( $rpc_url )->ethGetBlockByNumber( $block_number_or_tag );
	}

	/** @inheritdoc */
	public function getChainId( RpcUrl $rpc_url ): ChainId {
		return $this->client( $rpc_url )->ethChainId();
	}

	/** @inheritdoc */
	public function getBlockNumber( RpcUrl $rpc_url ): BlockNumber {
		return $this->client( $rpc_url )->ethBlockNumber();
	}

	/** @inheritdoc */
	public function getBalance( RpcUrl $rpc_url, Address $address ): Amount {
		return $this->client( $rpc_url )->ethGetBalance( $address );
	}

	/** @inheritdoc */
	public function getLogs( RpcUrl $rpc_url, array $filter ): array {
		return $this->client( $rpc_url )->ethGetLogs( $filter );
	}

	/** 呼び出し毎に `BlockchainClient` を生成します（インスタンスを保持しません） */
	private function client( RpcUrl $rpc_url ): BlockchainClient {
		return new BlockchainClient( $rpc_url, $this->rpc_transport );
	}
}
