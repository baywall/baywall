<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Dto\SalesHistoryDto;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetSalesHistoryDtos;

class SalesHistoriesResolver extends ResolverBase {

	private UserAccessChecker $user_access_checker;
	private GetSalesHistoryDtos $get_sales_history_dtos;

	public function __construct(
		UserAccessChecker $user_access_checker,
		GetSalesHistoryDtos $get_sales_history_dtos
	) {
		$this->user_access_checker    = $user_access_checker;
		$this->get_sales_history_dtos = $get_sales_history_dtos;
	}

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var string|null */
		$filter_invoice_id_value = $args['filter']['invoiceId'] ?? null;

		$sales_history_dtos = $this->get_sales_history_dtos->handle( $filter_invoice_id_value );

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
			$sales_history_dtos
		);
	}
}
