<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;
use Cornix\Serendipity\Core\Domain\Service\AppContractDataProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\AppContractClientFactory;

class AppContractDataProviderImpl implements AppContractDataProvider {

	private AppContractClientFactory $app_contract_client_factory;
	private ServerSignerRepository $server_signer_repository;

	public function __construct( AppContractClientFactory $app_contract_client_factory, ServerSignerRepository $server_signer_repository ) {
		$this->app_contract_client_factory = $app_contract_client_factory;
		$this->server_signer_repository    = $server_signer_repository;
	}

	/** 購入時のブロック番号を取得します */
	public function unlockedBlockNumber( ChainId $chain_id, PostId $post_id, Address $customer_address ): ?BlockNumber {
		$client         = $this->app_contract_client_factory->create( $chain_id );
		$signer_address = $this->server_signer_repository->get()->address();

		$res = $client->getPaywallStatus( $signer_address, $post_id, $customer_address );

		return $res->unlockedBlockNumber();
	}
}
