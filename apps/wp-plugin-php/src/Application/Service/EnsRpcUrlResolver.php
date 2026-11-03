<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\RpcUrl;

class EnsRpcUrlResolver {
	/** 本番用 ENS のチェーンID（Ethereum Mainnet） */
	private const ENS_CHAIN_ID_PRODUCTION = 1;

	public function __construct( private readonly ChainRepository $chain_repository ) {}

	/**
	 * ENS解決に使うEthereum MainnetのRPC URLを返します。RPC URL未登録時はnullを返します。
	 *
	 * @throws \RuntimeException Ethereum Mainnetのチェーンが未登録の場合
	 */
	public function resolve(): ?RpcUrl {
		$chain = $this->chain_repository->get( ChainId::from( self::ENS_CHAIN_ID_PRODUCTION ) );
		if ( null === $chain ) {
			throw new \RuntimeException( '[A76FEFEB] Chain not found. chain ID: ' . self::ENS_CHAIN_ID_PRODUCTION );
		}
		return $chain->rpcUrl();
	}
}
