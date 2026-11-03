<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\Repository\SearchCondition\SalesHistorySearchCondition;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;
use Baywall\Core\Constant\Config;
use InvalidArgumentException;

/**
 * 販売履歴の検索条件を組み立てます。salesHistories / salesHistoriesCount の両 UseCase で共有します。
 */
class SalesHistorySearchConditionFactory {
	/**
	 * フィルタ条件を検索条件へ設定します。
	 *
	 * @throws InvalidArgumentException 負のタイムスタンプ
	 */
	public function buildFilter( ?InvoiceId $invoice_id, ?ChainId $chain_id, ?TransactionHash $transaction_hash, ?int $date_from, ?int $date_to ): SalesHistorySearchCondition {
		$condition = new SalesHistorySearchCondition();
		$condition
			->setInvoiceId( $invoice_id )
			->setChainId( $chain_id )
			->setTransactionHash( $transaction_hash );

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
	public function applyPaging( SalesHistorySearchCondition $condition, ?int $limit, ?int $offset ): void {
		if ( $limit !== null && $limit < 0 ) {
			throw new InvalidArgumentException( '[2F71B5D8] limit must be a non-negative integer.' );
		}

		if ( $offset !== null && $offset < 0 ) {
			throw new InvalidArgumentException( '[E1B3CEC2] offset must be a non-negative integer.' );
		}

		$condition
			->setLimit( min( $limit ?? Config::SALES_HISTORIES_MAX_RESULTS, Config::SALES_HISTORIES_MAX_RESULTS ) )
			->setOffset( $offset );
	}
}
