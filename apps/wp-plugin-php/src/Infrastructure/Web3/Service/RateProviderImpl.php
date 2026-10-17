<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\Exception\RateNotFoundException;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Service\OracleService;
use Baywall\Core\Domain\Service\RateProvider;
use Baywall\Core\Domain\ValueObject\SymbolPair;
use Baywall\Core\Domain\ValueObject\Rate;
use Baywall\Core\Infrastructure\Cache\OracleRateCache;

class RateProviderImpl implements RateProvider {
	public function __construct(
		private readonly OracleService $oracle_service,
		private readonly OracleResolver $oracle_resolver,
		private readonly ChainRepository $chain_repository,
		private readonly OracleRateCache $oracle_rate_cache
	) {}

	/** @inheritdoc */
	public function getRate( SymbolPair $symbol_pair ): Rate {
		// 接続可能なオラクルを取得
		$oracle = $this->oracle_resolver->resolveRateOracle( $symbol_pair );
		if ( $oracle === null ) {
			// オラクルが見つからない場合は例外を投げる
			throw new RateNotFoundException( "[2C1C6F2A] No available oracle for symbol pair: {$symbol_pair->base()}-{$symbol_pair->quote()}" );
		}

		// キャッシュから取得（ヒット時はチェーン解決・RPC呼び出しを行わない）
		$rate = $this->oracle_rate_cache->get( $oracle );
		if ( $rate !== null ) {
			return $rate;
		}

		// オラクルがデプロイされているチェーンのRPC URLを解決
		$chain = $this->chain_repository->get( $oracle->chainId() );
		if ( null === $chain ) {
			throw new \RuntimeException( "[45FECC13] Chain not found. chain ID: {$oracle->chainId()}" );
		}
		$rpc_url = $chain->rpcUrl();
		if ( null === $rpc_url ) {
			throw new \RuntimeException( "[8B6B07F5] RPC URL not set. chain ID: {$oracle->chainId()}" );
		}

		// オラクルからレートを取得してキャッシュに保存
		$rate = $this->oracle_service->getRate( $rpc_url, $oracle->address() );
		$this->oracle_rate_cache->set( $oracle, $rate );

		return $rate;
	}

	/** @inheritdoc */
	public function supports( SymbolPair $symbol_pair ): bool {
		// 接続可能なオラクルを取得
		$oracle = $this->oracle_resolver->resolveRateOracle( $symbol_pair );
		return $oracle !== null;
	}
}
