<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\Entity\Invoice;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\InvoiceRepository;
use Baywall\Core\Domain\Repository\InvoiceTokenRepository;
use Baywall\Core\Domain\Repository\PausedRepository;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\Repository\SellerRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\InvoiceTokenString;
use Baywall\Core\Domain\ValueObject\PostId;

class InvoiceService {


	public function __construct(
		private readonly PostRepository $post_repository,
		private readonly PriceExchangeService $price_exchange_service,
		private readonly TokenAmountConverter $token_amount_converter,
		private readonly SellerRepository $seller_repository,
		private readonly InvoiceRepository $invoice_repository,
		private readonly InvoiceTokenRepository $invoice_token_repository,
		private readonly PausedRepository $paused_repository
	) {}

	/** 請求書トークンの文字列から請求書を取得します。 */
	public function getByInvoiceTokenString( InvoiceTokenString $invoice_token_string ): Invoice {

		$invoice_token = $this->invoice_token_repository->get( $invoice_token_string );
		if ( $invoice_token === null ) {
			throw new \InvalidArgumentException( "[BCD15F61] Invalid invoice token: {$invoice_token_string}" );
		}
		$invoice = $this->invoice_repository->get( $invoice_token->invoiceId() );
		if ( $invoice === null ) {
			throw new \InvalidArgumentException( "[AD1EB338] Invoice not found for invoice token: {$invoice_token_string}" );
		}

		return $invoice;
	}

	/** 請求書を発行します。 */
	public function issueInvoice( Address $buyer_address, PostId $post_id, Token $payment_token ): Invoice {
		$chain_id = $payment_token->chainId();
		$post     = $this->post_repository->get( $post_id );
		// TODO: 対象の投稿が購入可能かどうかをチェック

		$seller        = $this->seller_repository->get();
		$selling_price = $post->sellingPrice();

		// 事前チェック。ここを通らないように画面で制御すること
		if ( $this->paused_repository->get() ) {
			throw new \RuntimeException( '[F2EDAE53] paused.' );    // サイト一時停止状態
		} elseif ( $seller === null ) {
			throw new \RuntimeException( '[70C212C9] seller is null.' ); // 販売者未登録
		} elseif ( $selling_price === null ) {
			throw new \RuntimeException( "[45982ECE] selling_price is null. post_id: {$post_id}" ); // 販売価格未登録
		}

		// 支払い額を取得(この時点では 0,1 ETH のようなブロックチェーン上の小数点以下桁数が考慮されていない価格)
		$payment_price = $this->price_exchange_service->exchange( $selling_price, $payment_token->symbol() );

		// 支払うトークンの数量を計算
		$payment_amount = $this->token_amount_converter->convertPriceToBaseUnit( $payment_price, $chain_id );

		$invoice = new Invoice(
			InvoiceId::generate(), // 新規請求書ID
			$post_id,
			$chain_id,
			$selling_price,
			$seller->address(),
			$payment_token->address(),
			$payment_token->symbol(),
			$payment_token->decimals(),
			$payment_amount,
			$buyer_address
		);

		// 作成した請求書を保存
		$this->invoice_repository->save( $invoice );

		return $invoice;
	}
}
