<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class InvoiceService {

	private PostRepository $post_repository;
	private PriceExchangeService $price_exchange_service;
	private TokenAmountConverter $token_amount_converter;
	private SellerRepository $seller_repository;
	private InvoiceRepository $invoice_repository;

	public function __construct(
		PostRepository $post_repository,
		PriceExchangeService $price_exchange_service,
		TokenAmountConverter $token_amount_converter,
		SellerRepository $seller_repository,
		InvoiceRepository $invoice_repository
	) {
		$this->post_repository        = $post_repository;
		$this->price_exchange_service = $price_exchange_service;
		$this->token_amount_converter = $token_amount_converter;
		$this->seller_repository      = $seller_repository;
		$this->invoice_repository     = $invoice_repository;
	}

	/** 請求書を発行します。 */
	public function issueInvoice( Address $customer_address, PostId $post_id, Token $payment_token ): Invoice {
		$chain_id = $payment_token->chainId();
		$post     = $this->post_repository->get( $post_id );
		// TODO: 対象の投稿が購入可能かどうかをチェック

		$seller = $this->seller_repository->get();
		if ( $seller === null ) {
			throw new \RuntimeException( '[70C212C9] seller is null.' );
		}
		$selling_price = $post->sellingPrice();
		if ( $selling_price === null ) {
			throw new \RuntimeException( "[45982ECE] selling_price is null. post_id: {$post_id}" );
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
			$payment_amount,
			$customer_address
		);

		// 作成した請求書を保存
		$this->invoice_repository->save( $invoice );

		return $invoice;
	}
}
