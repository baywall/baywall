<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Application\Service\EthGetLogsToBlockProvider;
use Cornix\Serendipity\Core\Application\Service\ServerSignerService;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\AppContractClientFactory;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;

class CrawlAppContract {

	private BlockNumberProvider $block_number_provider;
	private AppContractRepository $app_contract_repository;
	private AppContractClientFactory $app_contract_client_factory;
	private EthGetLogsToBlockProvider $eth_get_logs_to_block_provider;
	private ServerSignerService $server_signer_service;
	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;

	public function __construct(
		BlockNumberProvider $block_number_provider,
		AppContractRepository $app_contract_repository,
		AppContractClientFactory $app_contract_client_factory,
		EthGetLogsToBlockProvider $eth_get_logs_to_block_provider,
		ServerSignerService $server_signer_service,
		UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository
	) {
		$this->block_number_provider                    = $block_number_provider;
		$this->app_contract_repository                  = $app_contract_repository;
		$this->app_contract_client_factory              = $app_contract_client_factory;
		$this->eth_get_logs_to_block_provider           = $eth_get_logs_to_block_provider;
		$this->server_signer_service                    = $server_signer_service;
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
	}

	public function handle( int $chain_id_value ): void {
		$chain_id     = ChainId::from( $chain_id_value );
		$app_contract = $this->app_contract_repository->get( $chain_id );

		// 現時点での最新ブロック番号を取得
		$latest_block_number = $this->block_number_provider->getByChainId( $chain_id );

		// 既にクロール済みのブロック番号を取得
		$crawled_block_number = $app_contract->crawledBlockNumber();
		assert( $crawled_block_number !== null, "[87EF1686] Crawled block number is null for chain ID: {$chain_id}" );

		//
		// TODO: 最後にクロールした時刻を現在時刻を比較して、一定時間経過していない場合はクロールしないようにする
		//

		// クロール対象のブロック番号を計算
		$from_block_number = $crawled_block_number->add( 1 );
		$to_block_number   = $this->eth_get_logs_to_block_provider->get( $chain_id, $from_block_number, $latest_block_number );

		// フィルタ条件となる署名用ウォレットアドレスを取得
		$server_signer_address = $this->server_signer_service->getServerSigner()->address();
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
