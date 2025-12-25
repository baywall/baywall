<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\InvoiceTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\InitCrawledBlockNumber;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\InvoiceTokenService;
use Cornix\Serendipity\Core\Domain\Service\PriceExchangeService;
use Cornix\Serendipity\Core\Domain\Service\TokenAmountConverter;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;
use Cornix\Serendipity\Core\Infrastructure\Format\SolidityStrings;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Terms\ConsumerTermsProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\SignatureService;
use phpseclib\Math\BigInteger;

class ResolveIssueInvoice {

	private InitCrawledBlockNumber $init_crawled_block_number;
	private UserAccessChecker $user_access_checker;
	private TransactionService $transaction_service;
	private TokenRepository $token_repository;
	private PostRepository $post_repository;
	private SellerRepository $seller_repository;
	private TokenAmountConverter $token_amount_converter;
	private PriceExchangeService $price_exchange_service;
	private InvoiceRepository $invoice_repository;
	private ServerSignerRepository $server_signer_repository;
	private SignatureService $signature_service;
	private InvoiceTokenService $invoice_token_service;
	private InvoiceTokenCookieProvider $invoice_token_cookie_provider;
	private CookieWriter $cookie_writer;

	public function __construct(
		InitCrawledBlockNumber $init_crawled_block_number,
		UserAccessChecker $user_access_checker,
		TransactionService $transaction_service,
		TokenRepository $token_repository,
		PostRepository $post_repository,
		SellerRepository $seller_repository,
		TokenAmountConverter $token_amount_converter,
		PriceExchangeService $price_exchange_service,
		InvoiceRepository $invoice_repository,
		ServerSignerRepository $server_signer_service,
		SignatureService $signature_service,
		InvoiceTokenService $invoice_token_service,
		InvoiceTokenCookieProvider $invoice_token_cookie_provider,
		CookieWriter $cookie_writer
	) {
		$this->init_crawled_block_number     = $init_crawled_block_number;
		$this->user_access_checker           = $user_access_checker;
		$this->transaction_service           = $transaction_service;
		$this->token_repository              = $token_repository;
		$this->post_repository               = $post_repository;
		$this->seller_repository             = $seller_repository;
		$this->token_amount_converter        = $token_amount_converter;
		$this->price_exchange_service        = $price_exchange_service;
		$this->invoice_repository            = $invoice_repository;
		$this->server_signer_repository      = $server_signer_service;
		$this->signature_service             = $signature_service;
		$this->invoice_token_service         = $invoice_token_service;
		$this->invoice_token_cookie_provider = $invoice_token_cookie_provider;
		$this->cookie_writer                 = $cookie_writer;
	}

	public function handle( array $root_value, array $args ) {
		$post_id          = PostId::from( $args['postId'] );
		$chain_id         = ChainId::from( $args['chainId'] );
		$token_address    = Address::from( $args['tokenAddress'] );
		$consumer_address = Address::from( $args['consumerAddress'] ); // 購入者のアドレス

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 請求書番号を発行(+現在の販売価格を記録)
		return $this->transaction_service->transactional(
			function () use ( $post_id, $chain_id, $token_address, $consumer_address ) {
				// 請求書を作成
				$invoice = $this->createInvoice( $post_id, $chain_id, $token_address, $consumer_address );
				// 請求書に対して署名を行う
				$signed_data = $this->signInvoice( $invoice );

				// クロール済みブロック番号を初期化
				$this->init_crawled_block_number->handle( $chain_id->value() );

				// 請求書トークンを発行し、Cookieに保存
				$invoice_token = $this->invoice_token_service->issue( $invoice->id() );
				$cookie        = $this->invoice_token_cookie_provider->get( $invoice_token );
				$this->cookie_writer->set( $cookie );

				return array(
					'invoiceIdHex'    => $invoice->id()->hex(),
					'serverMessage'   => $signed_data->message()->value(),
					'serverSignature' => $signed_data->signature()->value(),
					'paymentAmount'   => $invoice->paymentAmount()->value(),
				);
			}
		);
	}


	/** 請求書を作成します */
	private function createInvoice( PostId $post_id, ChainId $chain_id, Address $payment_token_address, Address $consumer_address ): Invoice {

		$payment_token  = $this->token_repository->get( $chain_id, $payment_token_address ); // 支払トークン
		$seller_address = $this->seller_repository->get()->address();
		$selling_price  = $this->post_repository->get( $post_id )->sellingPrice();
		if ( $selling_price === null ) {
			throw new \InvalidArgumentException( "[8AF88CAF] Selling price is null for post ID: {$post_id}" );
		}

		// 支払うトークンにおける価格を計算
		// ※ これは`1ETH`等の価格を表現するオブジェクトであり、実際に支払う数量(wei等)ではないことに注意
		$payment_price = $this->price_exchange_service->exchange( $selling_price, $payment_token->symbol() );
		// 支払うトークン量を取得
		$payment_amount = $this->token_amount_converter->convertPriceToBaseUnit( $payment_price, $chain_id );

		$invoice = new Invoice(
			InvoiceId::generate(), // 新規請求書ID
			$post_id,
			$chain_id,
			$selling_price,
			$seller_address,
			$payment_token_address,
			$payment_amount,
			$consumer_address,
		);
		assert( $this->invoice_repository->get( $invoice->id() ) === null, '[A9E90E49] Duplicate invoice ID detected.' );   // 請求書IDの重複チェック(存在しないIDが発行されていることを確認)

		// 請求書情報を保存
		$this->invoice_repository->save( $invoice );

		return $invoice;
	}

	/** 請求書に対して署名を行います */
	private function signInvoice( Invoice $invoice ): SignInvoiceResult {
		// 署名用ウォレットで署名を行うためのメッセージを作成
		$server_message = SigningMessage::from(
			SolidityStrings::valueToHexString( $invoice->chainId()->value() )
			. SolidityStrings::addressToHexString( $invoice->sellerAddress() )
			. SolidityStrings::addressToHexString( $invoice->consumerAddress() )
			. SolidityStrings::valueToHexString( $invoice->postId()->value() )
			. SolidityStrings::valueToHexString( $invoice->id()->hex() )
			. SolidityStrings::addressToHexString( $invoice->paymentTokenAddress() )
			. SolidityStrings::valueToHexString( new BigInteger( $invoice->paymentAmount()->value() ) )
			. SolidityStrings::valueToHexString( ( new ConsumerTermsProvider() )->getTextHash()->hex()->value() )
			. SolidityStrings::addressToHexString( Ethers::zeroAddress() )    // TODO: アフィリエイターのアドレス
			. SolidityStrings::valueToHexString( 0 )    // TODO: アフィリエイト報酬率
		);

		// サーバーの署名用ウォレットで署名
		$server_signer    = $this->server_signer_repository->get();
		$server_signature = $this->signature_service->signMessage( $server_signer, $server_message );

		return new SignInvoiceResult( $server_message, $server_signature );
	}
}

class SignInvoiceResult {
	public function __construct( SigningMessage $message, Signature $signature ) {
		$this->message   = $message;
		$this->signature = $signature;
	}

	private SigningMessage $message;
	private Signature $signature;

	public function message(): SigningMessage {
		return $this->message;
	}
	public function signature(): Signature {
		return $this->signature;
	}
}
