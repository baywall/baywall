<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

/**
 * `eth_getLogs`を呼び出す際の終了ブロック番号を計算するクラス
 */
class EthGetLogsToBlockProvider {

	private ChainRepository $chain_repository;

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	/**
	 * `eth_getLogs`を呼び出す際の終了ブロック番号を計算します。
	 *
	 * @param ChainId     $chain_id チェーンID
	 * @param BlockNumber $from_block 開始ブロック番号
	 * @param BlockNumber $latest_block 現時点での最新ブロック番号
	 * @return BlockNumber 終了ブロック番号
	 */
	public function get( ChainId $chain_id, BlockNumber $from_block, BlockNumber $latest_block ): BlockNumber {

		// チェーンに対応する待機数を取得
		$confirmations_value = $this->chain_repository->get( $chain_id )->confirmations()->value();
		if ( ! is_int( $confirmations_value ) ) {
			// 現在、confirmationsは整数の値のみ対応
			throw new \InvalidArgumentException( "[34F66AE3] Confirmations value must be an integer. {$confirmations_value}" );
		}

		// 一旦confirmationsを考慮せずに終了ブロック番号を計算
		/** @var int */
		$to_number_value = min(
			$from_block->int() + ( Config::GET_LOGS_MAX_RANGE - 1 ),
			$latest_block->int()
		);
		// confirmationsを考慮して終了ブロック番号を調整
		$to_number_value -= ( $confirmations_value - 1 );
		// 終了ブロック番号が開始ブロック番号を超えないようにする
		/** @var int */
		$to_number_value = max( $to_number_value, $from_block->int() );

		// BlockNumberオブジェクトに変換して返す
		return BlockNumber::fromInt( $to_number_value );
	}
}
