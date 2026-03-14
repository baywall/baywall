<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Exception\ChainConnectionException;
use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\AppContractClientFactory;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;

class AppContractCrawlService {

	private AppLogger $logger;
	private BlockNumberProvider $block_number_provider;
	private AppContractRepository $app_contract_repository;
	private AppContractClientFactory $app_contract_client_factory;
	private EthGetLogsToBlockProvider $eth_get_logs_to_block_provider;
	private ServerSignerRepository $server_signer_repository;
	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;

	public function __construct(
		AppLogger $logger,
		BlockNumberProvider $block_number_provider,
		AppContractRepository $app_contract_repository,
		AppContractClientFactory $app_contract_client_factory,
		EthGetLogsToBlockProvider $eth_get_logs_to_block_provider,
		ServerSignerRepository $server_signer_repository,
		UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository
	) {
		$this->logger                                   = $logger;
		$this->block_number_provider                    = $block_number_provider;
		$this->app_contract_repository                  = $app_contract_repository;
		$this->app_contract_client_factory              = $app_contract_client_factory;
		$this->eth_get_logs_to_block_provider           = $eth_get_logs_to_block_provider;
		$this->server_signer_repository                 = $server_signer_repository;
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
	}

	public function crawl( ChainId $chain_id ): void {

		$app_contract = $this->app_contract_repository->get( $chain_id );

		// 接続不可の場合は例外を投げる
		if ( ! $app_contract->chain()->connectable() ) {
			throw new ChainConnectionException( "[94375A4C] Cannot connect to chain with ID: {$chain_id}" );
		}

		// 現時点での最新ブロック番号を取得
		$latest_block_number = $this->block_number_provider->getByChainId( $chain_id );

		// 既にクロール済みのブロック番号を取得
		$crawled_block_number = $app_contract->crawledBlockNumber();
		assert( $crawled_block_number !== null, "[87EF1686] Crawled block number is null for chain ID: {$chain_id}" );

		// ログ出力
		$this->logger->debug( "[A5114D7B] chain id: {$chain_id}, crawled block number: {$crawled_block_number}, latest block number: {$latest_block_number}" );

		//
		// TODO: 最後にクロールした時刻を現在時刻を比較して、一定時間経過していない場合はクロールしないようにする
		//

		// クロール対象のブロック番号を計算
		$from_block_number = $crawled_block_number->add( 1 );
		$to_block_number   = $this->eth_get_logs_to_block_provider->get( $chain_id, $from_block_number, $latest_block_number );

		// フィルタ条件となる署名用ウォレットアドレスを取得
		$server_signer_address = $this->server_signer_repository->get()->address();
		// Appコントラクトからイベントを取得
		$client = $this->app_contract_client_factory->create( $chain_id );
		$events = $client->getUnlockPaywallTransferEvents( $from_block_number, $to_block_number, $server_signer_address );

		// イベント情報をDBに保存
		foreach ( $events as $event ) {
			$this->unlock_paywall_transfer_event_repository->save( $chain_id, $event );
		}

		// クロール済みブロック番号を更新
		$app_contract->setCrawledBlockNumber( $to_block_number );
		$this->app_contract_repository->save( $app_contract );
	}
}
