<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\Exception\LockAcquisitionException;
use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\CrawledBlockRepository;
use Baywall\Core\Domain\Repository\ServerSignerRepository;
use Baywall\Core\Domain\Repository\UnlockPaywallTransferEventRepository;
use Baywall\Core\Domain\Service\BlockchainService;
use Baywall\Core\Domain\ValueObject\BlockHeight;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;

class AppContractCrawlService {

	private const LOCK_NAME            = '6cb0475a-47f7-4468-b733-c7a97e34802b';
	private const LOCK_TIMEOUT_SECONDS = 0;


	public function __construct(
		private readonly AppLogger $logger,
		private readonly LockService $lock_service,
		private readonly TransactionService $transaction_service,
		private readonly BlockchainService $blockchain_service,
		private readonly CrawledBlockRepository $crawled_block_repository,
		private readonly AppContractClientProvider $app_contract_client_provider,
		private readonly ServerSignerRepository $server_signer_repository,
		private readonly UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository,
		private readonly ChainRepository $chain_repository
	) {}

	/**
	 * Appコントラクトのログを取得します
	 *
	 * @param ChainId[]|ChainId $chain_ids
	 */
	public function crawl( $chain_ids ): void {
		if ( $chain_ids instanceof ChainId ) {
			$chain_ids = array( $chain_ids );
		}
		assert( is_array( $chain_ids ), '[9D65CCC6]' );

		// ロックを取得してクロール処理を実行
		try {
			$this->lock_service->withLock(
				self::LOCK_NAME,
				fn () => $this->crawlChains( $chain_ids ),
				self::LOCK_TIMEOUT_SECONDS
			);
		} catch ( LockAcquisitionException $e ) {
			// 別のセッションで実行中の場合、ここを通る。
			$this->logger->debug( $e );
			// 再スローはしない
		}
	}

	private function crawlChains( array $chain_ids ): void {
		// チェーン情報を解決し、各チェーンのクロール終了ブロックを取得する
		// chain テーブルに存在しないチェーンはクロール対象外（オンデマンド呼び出しでは請求書の ChainId がそのまま渡るため）
		$chains           = array();
		$crawl_end_blocks = array();
		$crawl_finished   = array();
		foreach ( $chain_ids as $index => $chain_id ) {
			$chain = $this->chain_repository->get( $chain_id );
			if ( null === $chain ) {
				$this->logger->warn( "[D7A6B355] Chain with ID {$chain_id} does not exist. Skipping crawl for this chain." );
				$crawl_finished[ $index ] = true; // 対象外のチェーンはクロール完了とみなす
				continue;
			}
			$chains[ $index ]           = $chain;
			$crawl_end_blocks[ $index ] = $this->getCrawlEndBlockNumber( $chain );
			$crawl_finished[ $index ]   = false;
		}
		// フィルタ条件となる署名用ウォレットアドレスを取得
		$server_signer_address = $this->server_signer_repository->getAddress();

		// 全てのチェーンでクロールが完了するまで繰り返す
		while ( in_array( false, $crawl_finished, true ) ) {
			foreach ( $chain_ids as $index => $chain_id ) {
				if ( $crawl_finished[ $index ] ) {
					continue; // 既にクロールが完了しているチェーンはスキップ
				}

				$chain = $chains[ $index ];
				if ( $chain->connectable() === false ) {
					$this->logger->warn( "[11533C7A] Chain with ID {$chain_id} is not connectable. Skipping crawl for this chain." );
					$crawl_finished[ $index ] = true; // 接続不可のチェーンはクロール完了とみなす
					continue;
				}

				// クロール済みブロック番号が未初期化のチェーンはクロール対象外
				$crawled_block_number = $this->crawled_block_repository->get( $chain_id );
				if ( null === $crawled_block_number ) {
					$this->logger->warn( "[6CEB0942] Crawled block number is not initialized. Skipping crawl for chain id: {$chain_id}" );
					$crawl_finished[ $index ] = true; // 未初期化のチェーンはクロール完了とみなす
					continue;
				}

				// クロールの開始ブロック番号及び終了ブロック番号を計算
				$from_block      = $crawled_block_number->add( BlockHeight::from( 1 ) );
				$to_block        = $from_block->add( BlockHeight::from( $chain->maxLogsRange()->value() - 1 ) );
				$crawl_end_block = $crawl_end_blocks[ $index ];
				if ( $to_block->compare( $crawl_end_block ) > 0 ) {
					$to_block = $crawl_end_block; // オーバーランしないように調整
				}
				if ( $from_block->compare( $to_block ) > 0 ) {
					$this->logger->info( "[23FA101A] Crawl finished for chain id: {$chain_id}. from block: {$from_block} is greater than to block: {$to_block}" );
					$crawl_finished[ $index ] = true;
					continue; // クロール対象のブロックがない場合はスキップ
				}

				$this->logger->info( "[19189032] Crawl chain id: {$chain_id}, from block: {$from_block}, to block: {$to_block}" );

				// ブロックチェーンからイベントを取得
				$client = $this->app_contract_client_provider->create( $chain_id );
				try {
					$events = $client->getUnlockPaywallTransferEvents( $from_block, $to_block, $server_signer_address );
				} catch ( \Throwable $e ) {
					$this->logger->error( "[7C06C04A] Failed to get logs for chain id: {$chain_id}, from block: {$from_block}, to block: {$to_block}" );
					$this->logger->error( $e );
					$crawl_finished[ $index ] = true; // エラーが発生したチェーンは処理抜けのためにクロール完了とする
					continue;
				}

				// イベント情報をDBに保存
				$this->transaction_service->beginTransaction();
				try {
					// イベント情報をDBに保存
					foreach ( $events as $event ) {
						$this->unlock_paywall_transfer_event_repository->save( $chain_id, $event );
					}
					// クロール済みブロック番号を更新
					$this->crawled_block_repository->save( $chain_id, $to_block );

					$this->transaction_service->commit();
				} catch ( \Throwable $e ) {
					// ロールバック後にログ出力する（ログINSERTもトランザクションに参加するため、先にログ出力するとロールバックで消失する）
					$this->transaction_service->rollback();
					$this->logger->error( $e );
					$crawl_finished[ $index ] = true; // 保存に失敗したチェーンは処理抜けのためにクロール完了とする
				}
			}
		}
	}


	/** 販売履歴を取得する最終ブロック番号を取得します */
	private function getCrawlEndBlockNumber( Chain $chain ): BlockNumber {
		$rpc_url = $chain->rpcUrl();
		if ( null === $rpc_url ) {
			throw new \InvalidArgumentException( "[6DD872FD] Chain {$chain->id()} does not have a valid RPC URL." );
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
