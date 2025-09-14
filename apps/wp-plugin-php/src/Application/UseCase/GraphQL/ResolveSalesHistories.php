<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Dto\SalesHistoryDto;
use Cornix\Serendipity\Core\Application\Service\SalesHistoryService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

class ResolveSalesHistories {

	private UserAccessChecker $user_access_checker;
	private SalesHistoryService $sales_history_service;

	public function __construct(
		UserAccessChecker $user_access_checker,
		SalesHistoryService $sales_history_service
	) {
		$this->user_access_checker   = $user_access_checker;
		$this->sales_history_service = $sales_history_service;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$filter_invoice_id = InvoiceId::fromNullable( $args['filter']['invoiceId'] ?? null );

		$sales_histories = $this->sales_history_service->find( $filter_invoice_id );

		return array_map(
			fn ( SalesHistoryDto $sales_history_dto ) => array(
				'invoice'                => array(
					'id'            => $sales_history_dto->invoice->id,
					'createdAtUnix' => $sales_history_dto->invoice->created_at_unix,
					'postId'        => $sales_history_dto->invoice->post_id,
					'sellingPrice'  => array(
						'amount' => $sales_history_dto->invoice->selling_price->amount,
						'symbol' => $sales_history_dto->invoice->selling_price->symbol,
					),
					'chainId'       => $sales_history_dto->invoice->chain_id,
				),
				'postTitle'              => $sales_history_dto->post_title,
				'txHash'                 => $sales_history_dto->tx_hash,

				'consumerAddress'        => $sales_history_dto->consumer_address,
				'consumerPaymentPrice'   => array(
					'amount' => $sales_history_dto->consumer_payment_price->amount,
					'symbol' => $sales_history_dto->consumer_payment_price->symbol,
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
