<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\Service\CrawlableChainsService;
use Throwable;

/**
 * すべてのチェーンに対してAppコントラクトのイベント収集を行います
 */
class CrawlAllAppContract {
	private CrawlableChainsService $crawlable_chains_service;
	private AppContractCrawlService $app_contract_crawl_service;
	private TransactionService $transaction_service;

	public function __construct(
		CrawlableChainsService $crawlable_chains_service,
		AppContractCrawlService $app_contract_crawl_service,
		TransactionService $transaction_service
	) {
		$this->crawlable_chains_service   = $crawlable_chains_service;
		$this->app_contract_crawl_service = $app_contract_crawl_service;
		$this->transaction_service        = $transaction_service;
	}

	public function handle(): void {
		// Appコントラクトをクロール可能なチェーン一覧を取得
		$crawlable_chains = $this->crawlable_chains_service->getForAppContract();

		/** @var Throwable|null */
		$error = null; // 発生したエラーを保持するための変数
		foreach ( $crawlable_chains as $chain ) {
			// トランザクションはチェーン単位で行う
			try {
				$this->transaction_service->beginTransaction();

				// Appコントラクトのイベントを収集
				$this->app_contract_crawl_service->crawl( $chain->id() );

				$this->transaction_service->commit();
			} catch ( Throwable $e ) {
				// エラーが発生した場合はトランザクションをロールバック
				$this->transaction_service->rollback();
				$error = $e; // エラーを保持
				// ここでは再スローしない
			}
		}

		if ( $error !== null ) {
			// すべてのチェーンの処理が完了した後にエラーをスロー
			throw $error;
		}
	}
}
