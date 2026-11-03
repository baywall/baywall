<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Dto\SalesHistoryDto;
use Baywall\Core\Application\UseCase\GetSalesHistories;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;

class ResolveSalesHistories implements Resolver {
	public function __construct(
		private readonly GetSalesHistories $get_sales_histories
	) {}

	/** @return array<string, mixed>[] */
	public function handle( array $root_value, array $args ) {
		$sales_histories = $this->get_sales_histories->handle(
			InvoiceId::fromUlidValueNullable( $args['filter']['invoiceId'] ?? null ),
			ChainId::fromNullable( $args['filter']['chainId'] ?? null ),
			TransactionHash::fromNullable( $args['filter']['txHash'] ?? null ),
			isset( $args['filter']['dateFrom'] ) ? (int) $args['filter']['dateFrom'] : null,
			isset( $args['filter']['dateTo'] ) ? (int) $args['filter']['dateTo'] : null,
			isset( $args['limit'] ) ? (int) $args['limit'] : null,
			isset( $args['offset'] ) ? (int) $args['offset'] : null
		);

		return array_map(
			fn ( SalesHistoryDto $sales_history_dto ) => array(
				'invoice'                => array(
					'id'             => $sales_history_dto->invoice->id,
					'blockTimestamp' => $sales_history_dto->invoice->block_timestamp,
					'postId'         => $sales_history_dto->invoice->post_id,
					'sellingPrice'   => array(
						'amount' => $sales_history_dto->invoice->selling_price->amount,
						'symbol' => $sales_history_dto->invoice->selling_price->symbol,
					),
					'chainId'        => $sales_history_dto->invoice->chain_id,
					'chainName'      => $sales_history_dto->invoice->chain_name,
				),
				'postTitle'              => $sales_history_dto->post_title,
				'txHash'                 => $sales_history_dto->tx_hash,

				'buyerAddress'           => $sales_history_dto->buyer_address,
				'buyerPaymentPrice'      => array(
					'amount' => $sales_history_dto->buyer_payment_price->amount,
					'symbol' => $sales_history_dto->buyer_payment_price->symbol,
				),
				'contractAddress'        => $sales_history_dto->contract_address,
				'contractReceivedPrice'  => array(
					'amount' => $sales_history_dto->contract_received_price->amount,
					'symbol' => $sales_history_dto->contract_received_price->symbol,
				),
				'sellerAddress'          => $sales_history_dto->seller_address,
				'sellerReceivedPrice'    => array(
					'amount' => $sales_history_dto->seller_received_price->amount,
					'symbol' => $sales_history_dto->seller_received_price->symbol,
				),
				'affiliateAddress'       => $sales_history_dto->affiliate_address,
				'affiliateReceivedPrice' => $sales_history_dto->affiliate_received_price ? array(
					'amount' => $sales_history_dto->affiliate_received_price->amount,
					'symbol' => $sales_history_dto->affiliate_received_price->symbol,
				) : null,
			),
			$sales_histories
		);
	}
}
