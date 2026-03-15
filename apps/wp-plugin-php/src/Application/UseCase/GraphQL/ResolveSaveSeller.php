<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Entity\Seller;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Infrastructure\Terms\SellerTermsProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\SignatureService;

class ResolveSaveSeller {

	private UserAccessChecker $user_access_checker;
	private SellerRepository $seller_repository;
	private SellerTermsProvider $seller_terms_provider;
	private SignatureService $signature_service;
	private TransactionService $transaction_service;

	public function __construct(
		UserAccessChecker $user_access_checker,
		SellerRepository $seller_repository,
		SellerTermsProvider $seller_terms_provider,
		SignatureService $signature_service,
		TransactionService $transaction_service
	) {
		$this->user_access_checker   = $user_access_checker;
		$this->seller_repository     = $seller_repository;
		$this->seller_terms_provider = $seller_terms_provider;
		$this->signature_service     = $signature_service;
		$this->transaction_service   = $transaction_service;
	}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$address         = Address::from( $args['address'] );
		$signing_message = SigningMessage::from( $args['signingMessage'] );
		$signature       = Signature::from( $args['signature'] );

		// 現在の販売者向け利用規約メッセージと一致しない場合は例外を投げる
		$expected_message = $this->seller_terms_provider->getSigningMessage();
		if ( ! $signing_message->equals( $expected_message ) ) {
			throw new \InvalidArgumentException( "[08965E9C] Invalid signing message. Expected: {$expected_message}, got: {$signing_message}" );
		}

		// 署名からアドレスを復元し、指定されたアドレスと一致することを確認
		$recovered_address = $this->signature_service->recoverAddress( $signing_message, $signature );
		if ( ! $recovered_address->equals( $address ) ) {
			throw new \InvalidArgumentException( "[EA91F2D4] Address mismatch. Expected: {$address}, got: {$recovered_address}" );
		}

		// 販売者情報を保存
		return $this->transaction_service->transactional(
			function () use ( $address, $signing_message, $signature ) {
				$this->seller_repository->save(
					new Seller(
						$address,
						$signing_message,
						$signature,
					)
				);
				return true;
			}
		);
	}
}
