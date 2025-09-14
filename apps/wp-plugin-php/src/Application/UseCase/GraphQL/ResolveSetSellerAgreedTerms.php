<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Entity\Seller;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;
use Cornix\Serendipity\Core\Infrastructure\Terms\SellerTermsProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;

class ResolveSetSellerAgreedTerms {

	private UserAccessChecker $user_access_checker;
	private SellerRepository $seller_repository;
	private SellerTermsProvider $seller_terms_provider;

	public function __construct(
		UserAccessChecker $user_access_checker,
		SellerRepository $seller_repository,
		SellerTermsProvider $seller_terms_provider
	) {
		$this->user_access_checker   = $user_access_checker;
		$this->seller_repository     = $seller_repository;
		$this->seller_terms_provider = $seller_terms_provider;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$terms_version = TermsVersion::from( $args['version'] );
		$signature     = Signature::from( $args['signature'] );

		// 現在の販売者向け利用規約のバージョンと一致しない場合は例外を投げる
		$current_version = $this->seller_terms_provider->currentVersion();
		if ( $terms_version->equals( $current_version ) === false ) {
			throw new \InvalidArgumentException( "[F2CC693C] Invalid terms version. Expected: {$current_version}, got: {$terms_version}" );
		}

		// 署名用メッセージを取得
		$signing_message = $this->seller_terms_provider->getSigningMessage( $terms_version );
		$seller_address  = Ethers::verifyMessage( $signing_message, $signature );
		// TODO: 引数に string $expected_address を追加し、署名を検証するロジックを追加

		// 販売者情報を保存
		$this->seller_repository->save(
			new Seller(
				$seller_address,
				$terms_version,
				$signing_message,
				$signature,
			)
		);

		return true;
	}
}
