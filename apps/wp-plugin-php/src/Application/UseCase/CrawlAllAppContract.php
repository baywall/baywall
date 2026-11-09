<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Service\AppContractCrawlService;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Service\CrawlableChainsService;

/**
 * すべてのチェーンに対してAppコントラクトのイベント収集を行います
 */
class CrawlAllAppContract {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly CrawlableChainsService $crawlable_chains_service,
		private readonly AppContractCrawlService $app_contract_crawl_service
	) {}

	public function handle(): void {
		// Appコントラクトをクロール可能なチェーン一覧を取得
		$crawlable_chains = $this->crawlable_chains_service->getForAppContract();

		// チェーン単位の詳細は下位の AppContractCrawlService が記録するため、ここでは開始時の概要のみ記録する
		$chain_ids = implode( ',', array_map( fn ( Chain $chain ) => $chain->id()->value(), $crawlable_chains ) );
		$this->app_log_service->info( '[09C72F29] Crawl all app contracts started. chain count: ' . count( $crawlable_chains ) . ", chain ids: {$chain_ids}" );

		$this->app_contract_crawl_service->crawl( ...$crawlable_chains );
	}
}
