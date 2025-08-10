<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Domain\Entity\Seller;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;
use Cornix\Serendipity\Core\Infrastructure\Terms\SellerTermsProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;

class SaveSeller {

	private SellerRepository $seller_repository;
	private SellerTermsProvider $seller_terms_provider;

	public function __construct(
		SellerRepository $seller_repository,
		SellerTermsProvider $seller_terms_provider
	) {
		$this->seller_repository     = $seller_repository;
		$this->seller_terms_provider = $seller_terms_provider;
	}

	public function handle( int $agreed_terms_version_value, string $signature_value ): void {
		$terms_version = TermsVersion::from( $agreed_terms_version_value );
		$signature     = Signature::from( $signature_value );

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
	}
}
