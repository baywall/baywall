<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\AppContractCrawlService;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Service\CrawlableChainsService;

/**
 * すべてのチェーンに対してAppコントラクトのイベント収集を行います
 */
class CrawlAllAppContract {

	public function __construct(
		private readonly CrawlableChainsService $crawlable_chains_service,
		private readonly AppContractCrawlService $app_contract_crawl_service
	) {}

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
