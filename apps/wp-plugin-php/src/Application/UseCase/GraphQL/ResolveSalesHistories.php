<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Dto\SalesHistoryDto;
use Cornix\Serendipity\Core\Application\Service\SalesHistoryQueryService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\SearchCondition\SalesHistorySearchCondition;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use InvalidArgumentException;

class ResolveSalesHistories {

	private UserAccessChecker $user_access_checker;
	private SalesHistoryQueryService $sales_history_service;

	public function __construct(
		UserAccessChecker $user_access_checker,
		SalesHistoryQueryService $sales_history_service
	) {
		$this->user_access_checker   = $user_access_checker;
		$this->sales_history_service = $sales_history_service;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		// 検索条件の構築
		$condition = new SalesHistorySearchCondition();
		$condition->setInvoiceId(
			InvoiceId::fromUlidValueNullable( $args['filter']['invoiceId'] ?? null )
		);

		// 日付フィルタの取得とバリデーション
		$date_from = isset( $args['filter']['dateFrom'] ) ? (int) $args['filter']['dateFrom'] : null;
		$date_to   = isset( $args['filter']['dateTo'] ) ? (int) $args['filter']['dateTo'] : null;

		// バリデーション: 負のタイムスタンプは不正として例外をスロー
		if ( $date_from !== null && $date_from < 0 ) {
			throw new InvalidArgumentException( 'dateFrom must be a non-negative Unix timestamp.' );
		}
		if ( $date_to !== null && $date_to < 0 ) {
			throw new InvalidArgumentException( 'dateTo must be a non-negative Unix timestamp.' );
		}

		// バリデーション: dateFrom > dateTo の場合は自動入れ替え（ユーザー利便性のため）
		if ( $date_from !== null && $date_to !== null && $date_from > $date_to ) {
			[ $date_from, $date_to ] = array( $date_to, $date_from );
		}

		$condition
			->setDateFrom( $date_from )
			->setDateTo( $date_to );

		$sales_histories = $this->sales_history_service->find( $condition );

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
					'chainName'     => $sales_history_dto->invoice->chain_name,
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
