<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\IssuedInvoiceDto;
use Cornix\Serendipity\Core\Application\Service\ServerSignerService;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\PriceExchangeService;
use Cornix\Serendipity\Core\Domain\Service\SellerService;
use Cornix\Serendipity\Core\Domain\Service\TokenAmountConverter;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Calc\SolidityStrings;
use Cornix\Serendipity\Core\Repository\ConsumerTerms;
use phpseclib\Math\BigInteger;

class IssueInvoice {
	public function __construct( TokenRepository $token_repository, InvoiceRepository $invoice_repository, PostRepository $post_repository, SellerService $seller_service, TokenAmountConverter $token_amount_converter, PriceExchangeService $price_exchange_service, ServerSignerService $server_signer_service, WalletService $wallet_service ) {
		$this->invoice_repository     = $invoice_repository;
		$this->post_repository        = $post_repository;
		$this->seller_service         = $seller_service;
		$this->token_amount_converter = $token_amount_converter;
		$this->price_exchange_service = $price_exchange_service;
		$this->server_signer_service  = $server_signer_service;
		$this->wallet_service         = $wallet_service;
		$this->get_payment_token      = new GetPaymentToken( $token_repository );
	}
	private InvoiceRepository $invoice_repository;
	private PostRepository $post_repository;
	private SellerService $seller_service;
	private TokenAmountConverter $token_amount_converter;
	private PriceExchangeService $price_exchange_service;
	private GetPaymentToken $get_payment_token;
	private ServerSignerService $server_signer_service;
	private WalletService $wallet_service;

	public function handle( int $post_id_value, int $chain_id_value, string $payment_token_address_value, string $consumer_address_value ): IssuedInvoiceDto {
		$post_id               = PostId::from( $post_id_value );
		$chain_id              = ChainId::from( $chain_id_value );
		$payment_token_address = Address::from( $payment_token_address_value );
		$consumer_address      = Address::from( $consumer_address_value );

		// 請求書を作成
		$invoice = $this->createInvoice( $post_id, $chain_id, $payment_token_address, $consumer_address );
		// 請求書に対して署名を行う
		$signed_data = $this->signInvoice( $invoice );

		return new IssuedInvoiceDto(
			$invoice->id()->hex(),
			$invoice->nonce()->value(),
			$signed_data->message()->value(),
			$signed_data->signature()->value(),
			$invoice->paymentAmount()->value()
		);
	}

	/** 請求書を作成します */
	private function createInvoice( PostId $post_id, ChainId $chain_id, Address $payment_token_address, Address $consumer_address ): Invoice {

		$payment_token  = $this->get_payment_token->handle( $chain_id, $payment_token_address ); // 支払トークン
		$seller_address = $this->seller_service->getSellerAddress();  // 販売者アドレス
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
			InvoiceNonce::generate() // 新規nonce
		);
		assert( $this->invoice_repository->get( $invoice->id() ) === null, '[A9E90E49] Duplicate invoice ID detected.' );   // 請求書IDの重複チェック(存在しないIDが発行されていることを確認)

		// 請求書情報を保存
		$this->invoice_repository->save( $invoice );

		return $invoice;
	}

	/** 請求書に対して署名を行います */
	private function signInvoice( Invoice $invoice ): SingInvoiceResult {
		// 署名用ウォレットで署名を行うためのメッセージを作成
		$server_message = SigningMessage::from(
			SolidityStrings::valueToHexString( $invoice->chainId()->value() )
			. SolidityStrings::addressToHexString( $invoice->sellerAddress() )
			. SolidityStrings::addressToHexString( $invoice->consumerAddress() )
			. SolidityStrings::valueToHexString( $invoice->id()->hex() )
			. SolidityStrings::valueToHexString( $invoice->postId()->value() )
			. SolidityStrings::addressToHexString( $invoice->paymentTokenAddress() )
			. SolidityStrings::valueToHexString( new BigInteger( $invoice->paymentAmount()->value() ) )
			. SolidityStrings::valueToHexString( ( new ConsumerTerms() )->currentVersion() )
			. SolidityStrings::addressToHexString( Ethers::zeroAddress() )    // TODO: アフィリエイターのアドレス
			. SolidityStrings::valueToHexString( 0 )    // TODO: アフィリエイト報酬率
		);

		// サーバーの署名用ウォレットで署名
		$server_signer    = $this->server_signer_service->getServerSigner();
		$server_signature = $this->wallet_service->signMessage( $server_signer, $server_message );

		return new SingInvoiceResult( $server_message, $server_signature );
	}
}

/**
 * 指定されたチェーンID、アドレスのトークン情報を取得します。
 *
 * @internal
 */
class GetPaymentToken {
	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository = $token_repository;
	}

	private TokenRepository $token_repository;

	public function handle( ChainId $chain_id, Address $token_address ): Token {
		$token = $this->token_repository->get( $chain_id, $token_address );
		if ( is_null( $token ) || ! $token->isPayable() ) {
			throw new \InvalidArgumentException( '[9213F631] The specified token is not payable.' );
		}
		return $token;
	}
}

/** @internal */
class SingInvoiceResult {
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
