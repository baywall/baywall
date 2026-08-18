<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\Repository\ServerSignerRepository;
use Baywall\Core\Domain\Service\AppContractDataProvider;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Infrastructure\Web3\Factory\AppContractClientFactory;

class AppContractDataProviderImpl implements AppContractDataProvider {

	private AppContractClientFactory $app_contract_client_factory;
	private ServerSignerRepository $server_signer_repository;

	public function __construct( AppContractClientFactory $app_contract_client_factory, ServerSignerRepository $server_signer_repository ) {
		$this->app_contract_client_factory = $app_contract_client_factory;
		$this->server_signer_repository    = $server_signer_repository;
	}

	/** 購入時のブロック番号を取得します */
	public function unlockedBlockNumber( ChainId $chain_id, PostId $post_id, Address $buyer_address ): ?BlockNumber {
		$client         = $this->app_contract_client_factory->create( $chain_id );
		$signer_address = $this->server_signer_repository->get()->address();

		$res = $client->getPaywallStatus( $signer_address, $post_id, $buyer_address );

		return $res->unlockedBlockNumber();
	}
}
