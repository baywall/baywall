<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\InvoiceTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\InitCrawledBlockNumber;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Entity\ServerSigner;
use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\InvoiceService;
use Cornix\Serendipity\Core\Domain\Service\InvoiceTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Bytes;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;
use Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers\EthersSigningKey;

class ResolveIssueInvoiceV2 {

	private UserAccessChecker $user_access_checker;
	private TransactionService $transaction_service;
	private InvoiceService $invoice_service;
	private TokenRepository $token_repository;
	private ServerSignerRepository $server_signer_repository;
	private InitCrawledBlockNumber $init_crawled_block_number;
	private InvoiceTokenService $invoice_token_service;
	private InvoiceTokenCookieProvider $invoice_token_cookie_provider;
	private CookieWriter $cookie_writer;

	public function __construct(
		UserAccessChecker $user_access_checker,
		TransactionService $transaction_service,
		InvoiceService $invoice_service,
		TokenRepository $token_repository,
		ServerSignerRepository $server_signer_repository,
		InitCrawledBlockNumber $init_crawled_block_number,
		InvoiceTokenService $invoice_token_service,
		InvoiceTokenCookieProvider $invoice_token_cookie_provider,
		CookieWriter $cookie_writer
	) {
		$this->user_access_checker           = $user_access_checker;
		$this->transaction_service           = $transaction_service;
		$this->invoice_service               = $invoice_service;
		$this->token_repository              = $token_repository;
		$this->server_signer_repository      = $server_signer_repository;
		$this->init_crawled_block_number     = $init_crawled_block_number;
		$this->invoice_token_service         = $invoice_token_service;
		$this->invoice_token_cookie_provider = $invoice_token_cookie_provider;
		$this->cookie_writer                 = $cookie_writer;
	}

	public function handle( array $root_value, array $args ) {
		$post_id          = PostId::from( $args['postId'] );
		$chain_id         = ChainId::from( $args['chainId'] );
		$token_address    = Address::from( $args['tokenAddress'] );
		$customer_address = Address::from( $args['customerAddress'] ); // 購入者のアドレス

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		$payment_token = $this->token_repository->get( $chain_id, $token_address );
		if ( $payment_token === null ) {
			throw new \InvalidArgumentException( "[BA148318] Payment token not found for chain ID: {$chain_id} and address: {$token_address}" );
		}
		$server_signer = $this->server_signer_repository->get();
		if ( $server_signer === null ) {
			throw new \RuntimeException( '[DA891FEC] Server signer is not configured.' );
		}

		// 請求書番号を発行(+現在の販売価格を記録)
		return $this->transaction_service->transactional(
			function () use ( $post_id, $payment_token, $customer_address ) {
				// 署名用ウォレットを取得
				$signer = $this->server_signer_repository->get();

				// 請求書を作成
				$invoice = $this->invoice_service->issueInvoice( $customer_address, $post_id, $payment_token );

				// 署名対象となるメッセージを作成
				$signing_message_bytes = $this->createSigningMessageBytes( $signer, $invoice );
				// メッセージに対して署名
				$signature = $this->signMessageBytes( $signer, $signing_message_bytes );

				// クロール済みブロック番号を初期化
				$this->init_crawled_block_number->handle( $payment_token->chainId()->value() );

				// 請求書トークンを発行し、Cookieに保存
				$invoice_token = $this->invoice_token_service->issue( $invoice->id() );
				$cookie        = $this->invoice_token_cookie_provider->get( $invoice_token );
				$this->cookie_writer->set( $cookie );

				return array(
					'message'              => $signing_message_bytes->hex()->value(),
					'signature'            => $signature->hex()->value(),
					'paymentAmount'        => $invoice->paymentAmount()->value(),
				);
			}
		);
	}

	/** 署名用のメッセージを作成します */
	private function createSigningMessageBytes( ServerSigner $server_signer, Invoice $invoice ): Bytes {
		$types  = array(
			'uint64',
			'address',
			'address',
			'address',
			'uint64',
			'uint128',
			'address',
			'uint256',
		);
		$values = array(
			$invoice->chainId()->value(),
			$server_signer->address()->value(),
			$invoice->sellerAddress()->value(),
			$invoice->customerAddress()->value(),
			$invoice->postId()->value(),
			$invoice->id()->hex(),
			$invoice->paymentTokenAddress()->value(),
			$invoice->paymentAmount()->hex()->value(),
		);

		return Bytes::fromHex( Hex::from( Ethers::solidityPacked( $types, $values ) ) );
	}

	/** 署名用メッセージに署名します */
	private function signMessageBytes( ServerSigner $server_signer, Bytes $message_bytes ): Signature {
		$signing_key = new EthersSigningKey( $server_signer->privateKey()->value() );
		return Signature::from( $signing_key->sign( Ethers::keccak256( $message_bytes->hex()->value() ) ) );
	}
}
