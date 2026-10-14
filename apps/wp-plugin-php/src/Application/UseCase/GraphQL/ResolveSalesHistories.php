<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Dto\SalesHistoryDto;
use Baywall\Core\Application\Service\SalesHistoryQueryService;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\SearchCondition\SalesHistorySearchCondition;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;
use Baywall\Core\Constant\Config;
use InvalidArgumentException;

class ResolveSalesHistories {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly SalesHistoryQueryService $sales_history_service
	) {}

	/**
	 * GraphQL 引数から検索条件を構築します。salesHistories / salesHistoriesCount の両リゾルバで共有します。
	 *
	 * @throws InvalidArgumentException 負のタイムスタンプ、負の limit / offset
	 */
	public static function buildSearchCondition( array $args ): SalesHistorySearchCondition {
		$condition = new SalesHistorySearchCondition();
		$condition
			->setInvoiceId( InvoiceId::fromUlidValueNullable( $args['filter']['invoiceId'] ?? null ) )
			->setChainId( ChainId::fromNullable( $args['filter']['chainId'] ?? null ) )
			->setTransactionHash( TransactionHash::fromNullable( $args['filter']['txHash'] ?? null ) );

		// 日付フィルタの取得とバリデーション
		$date_from = isset( $args['filter']['dateFrom'] ) ? (int) $args['filter']['dateFrom'] : null;
		$date_to   = isset( $args['filter']['dateTo'] ) ? (int) $args['filter']['dateTo'] : null;

		// バリデーション: 負のタイムスタンプは不正として例外をスロー
		if ( $date_from !== null && $date_from < 0 ) {
			throw new InvalidArgumentException( '[4C2EC629] dateFrom must be a non-negative Unix timestamp.' );
		}
		if ( $date_to !== null && $date_to < 0 ) {
			throw new InvalidArgumentException( '[BC0B4AE7] dateTo must be a non-negative Unix timestamp.' );
		}

		// バリデーション: dateFrom > dateTo の場合は自動入れ替え（ユーザー利便性のため）
		if ( $date_from !== null && $date_to !== null && $date_from > $date_to ) {
			[ $date_from, $date_to ] = array( $date_to, $date_from );
		}

		$condition
			->setDateFrom( $date_from )
			->setDateTo( $date_to );

		return $condition;
	}

	/**
	 * limit / offset を正規化して検索条件へ設定します。
	 *
	 * - 負数は例外
	 * - null はデフォルト（最大件数）へ正規化
	 * - limit = 0 は 0 件取得を許容
	 * - 上限超過は最大件数へクランプ
	 *
	 * @throws InvalidArgumentException limit / offset が負の場合
	 */
	public static function applyPaging( SalesHistorySearchCondition $condition, array $args ): void {
		$limit = isset( $args['limit'] ) ? (int) $args['limit'] : null;
		if ( $limit !== null && $limit < 0 ) {
			throw new InvalidArgumentException( '[2F71B5D8] limit must be a non-negative integer.' );
		}

		$offset = isset( $args['offset'] ) ? (int) $args['offset'] : null;
		if ( $offset !== null && $offset < 0 ) {
			throw new InvalidArgumentException( '[E1B3CEC2] offset must be a non-negative integer.' );
		}

		$condition
			->setLimit( min( $limit ?? Config::SALES_HISTORIES_MAX_RESULTS, Config::SALES_HISTORIES_MAX_RESULTS ) )
			->setOffset( $offset );
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		// 検索条件の構築
		$condition = self::buildSearchCondition( $args );
		self::applyPaging( $condition, $args );

		$sales_histories = $this->sales_history_service->find( $condition );

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
