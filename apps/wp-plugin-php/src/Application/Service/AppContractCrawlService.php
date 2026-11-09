<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\Exception\LockAcquisitionException;
use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Port\AdminNotificationService;
use Baywall\Core\Application\Port\AppContractClientFactory;
use Baywall\Core\Application\Port\CrawlFailureNotificationMessageProvider;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\CrawledBlockRepository;
use Baywall\Core\Domain\Repository\ServerSignerRepository;
use Baywall\Core\Application\Repository\UnlockPaywallTransferEventRepository;
use Baywall\Core\Domain\Service\BlockchainService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\BlockHeight;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\UnlockPaywallTransferEvent;

class AppContractCrawlService {

	private const LOCK_NAME            = '6cb0475a-47f7-4468-b733-c7a97e34802b';
	private const LOCK_TIMEOUT_SECONDS = 0;

	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly LockRunner $lock_runner,
		private readonly TransactionRunner $transaction_runner,
		private readonly BlockchainService $blockchain_service,
		private readonly CrawledBlockRepository $crawled_block_repository,
		private readonly AppContractClientFactory $app_contract_client_factory,
		private readonly ServerSignerRepository $server_signer_repository,
		private readonly UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository,
		private readonly ChainRepository $chain_repository,
		private readonly AdminNotificationService $admin_notification_service,
		private readonly CrawlFailureNotificationMessageProvider $crawl_failure_notification_message_provider
	) {}

	/** Appコントラクトのログを取得します */
	public function crawl( Chain ...$chains ): void {
		// ロックを取得してクロール処理を実行
		try {
			$this->lock_runner->withLock(
				self::LOCK_NAME,
				fn () => $this->crawlChains( $chains ),
				self::LOCK_TIMEOUT_SECONDS
			);
		} catch ( LockAcquisitionException $e ) {
			// 別のセッションで実行中の場合、ここを通る。
			$this->app_log_service->debug( $e );
			// 再スローはしない
		}
	}

	/** @param Chain[] $chains */
	private function crawlChains( array $chains ): void {
		// フィルタ条件となる署名用ウォレットアドレスを取得
		$server_signer_address = $this->server_signer_repository->getAddress();

		foreach ( $chains as $chain ) {
			if ( $chain->hasReachedConsecutiveFailureLimit() ) {
				// 上限到達で除外済みのチェーンは RPC を呼び出さずスキップする
				$this->app_log_service->warn( "[63C90281] Chain with ID {$chain->id()} has reached the consecutive failure limit. Skipping crawl for this chain." );
				continue;
			}

			// chain テーブルに存在しないチェーンは呼び出し側で除外済み
			try {
				$crawl_end_block = $this->getCrawlEndBlockNumber( $chain );
			} catch ( \Throwable $e ) {
				// 終了ブロックの取得に失敗したチェーンのみスキップし、クロール全体は続行する
				$this->app_log_service->error( "[BBDF5CEF] Failed to get crawl end block number for chain id: {$chain->id()}" );
				$this->app_log_service->error( $e );
				$this->handleCrawlFailure( $chain );
				continue;
			}

			$this->crawlChain( $chain, $crawl_end_block, $server_signer_address );
		}
	}

	/** 1チェーンのクロールを終了ブロックまで繰り返します */
	private function crawlChain( Chain $chain, BlockNumber $crawl_end_block, Address $server_signer_address ): void {
		while ( true ) {
			// クロール済みブロック番号が未初期化のチェーンはクロール対象外
			$crawled_block_number = $this->crawled_block_repository->get( $chain->id() );
			if ( null === $crawled_block_number ) {
				$this->app_log_service->warn( "[6CEB0942] Crawled block number is not initialized. Skipping crawl for chain id: {$chain->id()}" );
				return;
			}

			// クロールの開始ブロック番号及び終了ブロック番号を計算
			$from_block = $crawled_block_number->add( BlockHeight::from( 1 ) );
			$to_block   = $from_block->add( BlockHeight::from( $chain->maxLogsRange()->value() - 1 ) );
			if ( $to_block->compare( $crawl_end_block ) > 0 ) {
				$to_block = $crawl_end_block; // オーバーランしないように調整
			}
			if ( $from_block->compare( $to_block ) > 0 ) {
				$this->app_log_service->info( "[23FA101A] Crawl finished for chain id: {$chain->id()}. from block: {$from_block} is greater than to block: {$to_block}" );
				$this->resetConsecutiveFailuresIfNeeded( $chain );
				return; // クロール対象のブロックがない場合は完了
			}

			$this->app_log_service->info( "[19189032] Crawl chain id: {$chain->id()}, from block: {$from_block}, to block: {$to_block}" );

			$events = $this->fetchEvents( $chain, $from_block, $to_block, $server_signer_address );
			if ( null === $events ) {
				return;
			}

			if ( ! $this->saveEvents( $chain, $events, $from_block, $to_block ) ) {
				return;
			}
		}
	}

	/**
	 * ブロックチェーンからイベントを取得します。
	 *
	 * @return null|UnlockPaywallTransferEvent[] 失敗時は null（失敗処理済み）
	 */
	private function fetchEvents( Chain $chain, BlockNumber $from_block, BlockNumber $to_block, Address $server_signer_address ): ?array {
		try {
			$client = $this->app_contract_client_factory->create( $chain->id() );
			return $client->getUnlockPaywallTransferEvents( $from_block, $to_block, $server_signer_address );
		} catch ( \Throwable $e ) {
			$this->app_log_service->error( "[7C06C04A] Failed to get logs for chain id: {$chain->id()}, from block: {$from_block}, to block: {$to_block}" );
			$this->app_log_service->error( $e );
			$this->handleCrawlFailure( $chain );
			return null;
		}
	}

	/**
	 * イベント情報をDBに保存します。
	 *
	 * @param UnlockPaywallTransferEvent[] $events
	 * @return bool 成功時は true、失敗時は false （ログ出力のみで連続失敗回数は加算しない）
	 */
	private function saveEvents( Chain $chain, array $events, BlockNumber $from_block, BlockNumber $to_block ): bool {
		try {
			$this->transaction_runner->transactional(
				function () use ( $chain, $events, $to_block ) {
					// イベント情報をDBに保存
					foreach ( $events as $event ) {
						$this->unlock_paywall_transfer_event_repository->save( $chain->id(), $event );
					}
					// クロール済みブロック番号を更新
					$this->crawled_block_repository->save( $chain->id(), $to_block );
				}
			);
			return true;
		} catch ( \Throwable $e ) {
			// transactional() がロールバック済みのため、ログINSERTが消失しない（ログINSERTもトランザクションに参加するため）
			// DB書き込みエラーはログ出力のみ（連続失敗回数は加算しない）
			$this->app_log_service->error( "[75DA6932] Failed to save events for chain id: {$chain->id()}, from block: {$from_block}, to block: {$to_block}" );
			$this->app_log_service->error( $e );
			return false;
		}
	}

	/** チェーンのクロールが最後まで完了した際に連続失敗回数をリセットします */
	private function resetConsecutiveFailuresIfNeeded( Chain $chain ): void {
		if ( $chain->consecutiveFailures() > 0 ) {
			// チェーンの処理が最後まで完了したため連続失敗回数をリセットする（commit 後のトランザクション外）
			$chain->resetConsecutiveFailures();
			$this->chain_repository->saveConsecutiveFailures( $chain );
		}
	}

	/**
	 * チェーンのクロール失敗を共通処理します。
	 * 連続失敗回数を加算し、上限に達した場合のみ管理者へ通知します。
	 */
	private function handleCrawlFailure( Chain $chain ): void {
		$chain->incrementConsecutiveFailures();
		$this->chain_repository->saveConsecutiveFailures( $chain );
		if ( $chain->hasReachedConsecutiveFailureLimit() ) {
			// 上限到達後は初期ループでスキップされるため、この遷移でのみ通知される
			$this->admin_notification_service->notify(
				$this->crawl_failure_notification_message_provider->title(),
				$this->crawl_failure_notification_message_provider->message( $chain->name(), $chain->id() )
			);
		}
	}

	/** 販売履歴を取得する最終ブロック番号を取得します */
	private function getCrawlEndBlockNumber( Chain $chain ): BlockNumber {
		$rpc_url = $chain->rpcUrl();
		if ( null === $rpc_url ) {
			throw new \InvalidArgumentException( "[6FAC89E4] Chain {$chain->id()} does not have a valid RPC URL." );
		}

		// 現時点での最新ブロック番号を取得
		$latest_block_number = $this->blockchain_service->getBlockNumber( $rpc_url );
		// 該当チェーンの待機ブロック数を取得
		$confirmations = $chain->confirmations();

		// 待機ブロック数を考慮して終了ブロック番号を計算して返す
		// 例: confirmationsが3の場合、最新ブロックから2ブロック前までをクロール対象とする
		return $latest_block_number->sub( BlockHeight::from( $confirmations->value() - 1 ) );
	}
}
