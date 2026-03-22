<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Exception\LockAcquisitionException;
use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;
use Cornix\Serendipity\Core\Domain\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\AppContractClientFactory;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;

class AppContractCrawlService {

	private const LOCK_NAME            = '6cb0475a-47f7-4468-b733-c7a97e34802b';
	private const LOCK_TIMEOUT_SECONDS = 0;

	private AppLogger $logger;
	private LockService $lock_service;
	private TransactionService $transaction_service;
	private BlockNumberProvider $block_number_provider;
	private AppContractRepository $app_contract_repository;
	private AppContractClientFactory $app_contract_client_factory;
	private ServerSignerRepository $server_signer_repository;
	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;
	private ChainRepository $chain_repository;

	public function __construct(
		AppLogger $logger,
		LockService $lock_service,
		TransactionService $transaction_service,
		BlockNumberProvider $block_number_provider,
		AppContractRepository $app_contract_repository,
		AppContractClientFactory $app_contract_client_factory,
		ServerSignerRepository $server_signer_repository,
		UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository,
		ChainRepository $chain_repository
	) {
		$this->logger                                   = $logger;
		$this->lock_service                             = $lock_service;
		$this->transaction_service                      = $transaction_service;
		$this->block_number_provider                    = $block_number_provider;
		$this->app_contract_repository                  = $app_contract_repository;
		$this->app_contract_client_factory              = $app_contract_client_factory;
		$this->server_signer_repository                 = $server_signer_repository;
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
		$this->chain_repository                         = $chain_repository;
	}

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
		// 各ブロックのクロール終了ブロックを取得
		$crawl_end_blocks = array_map( fn ( ChainId $chain_id ) => $this->getCrawlEndBlockNumber( $chain_id ), $chain_ids );
		// 各チェーンでクロールが完了したかどうかのフラグ
		$crawl_finished = array_map( fn ( ChainId $chain_id ) => false, $chain_ids );
		// フィルタ条件となる署名用ウォレットアドレスを取得
		$server_signer_address = $this->server_signer_repository->get()->address();

		// 全てのチェーンでクロールが完了するまで繰り返す
		while ( in_array( false, $crawl_finished, true ) ) {
			foreach ( $chain_ids as $index => $chain_id ) {
				if ( $crawl_finished[ $index ] ) {
					continue; // 既にクロールが完了しているチェーンはスキップ
				} elseif ( $this->chain_repository->get( $chain_id )->connectable() === false ) {
					$this->logger->warn( "[11533C7A] Chain with ID {$chain_id} is not connectable. Skipping crawl for this chain." );
					$crawl_finished[ $index ] = true; // 接続不可のチェーンはクロール完了とみなす
					continue;
				}

				// クロールの開始ブロック番号及び終了ブロック番号を計算
				$from_block      = $this->app_contract_repository->get( $chain_id )->crawledBlockNumber()->add( 1 );
				$to_block        = $from_block->add( Config::GET_LOGS_MAX_RANGE - 1 );
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
				$client = $this->app_contract_client_factory->create( $chain_id );
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
					$app_contract = $this->app_contract_repository->get( $chain_id );
					$app_contract->setCrawledBlockNumber( $to_block );
					$this->app_contract_repository->save( $app_contract );

					$this->transaction_service->commit();
				} catch ( \Throwable $e ) {
					$this->logger->error( $e );
					$this->transaction_service->rollBack();
				}
			}
		}
	}


	/** 販売履歴を取得する最終ブロック番号を取得します */
	private function getCrawlEndBlockNumber( ChainId $chain_id ): BlockNumber {
		// 現時点での最新ブロック番号を取得
		$latest_block_number = $this->block_number_provider->getByChainId( $chain_id );
		// 該当チェーンの待機ブロック数を取得
		$confirmations = $this->chain_repository->get( $chain_id )->confirmations();

		assert( is_int( $confirmations->value() ), '[0683F910]' ); // confirmationsは整数のみ対応

		// 待機ブロック数を考慮して終了ブロック番号を計算して返す
		// 例: confirmationsが3の場合、最新ブロックから2ブロック前までをクロール対象とする
		return $latest_block_number->sub( $confirmations->value() - 1 );
	}
}
