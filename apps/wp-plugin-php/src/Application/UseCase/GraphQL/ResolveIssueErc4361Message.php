<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\Erc4361NonceRepository;
use Cornix\Serendipity\Core\Application\Service\Erc4361NonceProvider;
use Cornix\Serendipity\Core\Application\Service\Erc4361Service;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

class ResolveIssueErc4361Message {

	private TransactionService $transaction_service;
	private Erc4361NonceRepository $erc4361_nonce_repository;
	private Erc4361NonceProvider $erc4361_nonce_provider;
	private Erc4361Service $erc4361_service;

	public function __construct( TransactionService $transaction_service, Erc4361NonceRepository $erc4361_nonce_repository, Erc4361NonceProvider $erc4361_nonce_provider, Erc4361Service $erc4361_service ) {
		$this->transaction_service      = $transaction_service;
		$this->erc4361_nonce_repository = $erc4361_nonce_repository;
		$this->erc4361_nonce_provider   = $erc4361_nonce_provider;
		$this->erc4361_service          = $erc4361_service;
	}

	public function handle( array $root_value, array $args ) {
		$address  = Address::from( $args['address'] );
		$chain_id = ChainId::from( $args['chainId'] );

		return $this->transaction_service->transactional(
			function () use ( $address, $chain_id ) {
				// nonceを生成し、アドレスと紐づけて保存
				$nonce = $this->erc4361_nonce_provider->generate();
				$this->erc4361_nonce_repository->save( $address, $nonce );

				// 署名用メッセージを生成
				$erc4361_message = $this->erc4361_service->createMessage( $address, $chain_id, $nonce );

				return array(
					'message' => $erc4361_message->value(),
				);
			}
		);
	}
}
