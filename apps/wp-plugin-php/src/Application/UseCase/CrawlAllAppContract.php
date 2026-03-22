<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Service\CrawlableChainsService;

/**
 * すべてのチェーンに対してAppコントラクトのイベント収集を行います
 */
class CrawlAllAppContract {
	private CrawlableChainsService $crawlable_chains_service;
	private AppContractCrawlService $app_contract_crawl_service;

	public function __construct(
		CrawlableChainsService $crawlable_chains_service,
		AppContractCrawlService $app_contract_crawl_service
	) {
		$this->crawlable_chains_service   = $crawlable_chains_service;
		$this->app_contract_crawl_service = $app_contract_crawl_service;
	}

	public function handle(): void {
		// Appコントラクトをクロール可能なチェーン一覧を取得
		$crawlable_chains = $this->crawlable_chains_service->getForAppContract();

		$chain_ids = array_map(
			fn ( Chain $chain ) => $chain->id(),
			$crawlable_chains
		);
		$this->app_contract_crawl_service->crawl( $chain_ids );
	}
}
