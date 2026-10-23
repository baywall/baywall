<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\CrawledBlockRepository;
use Baywall\Core\Domain\Service\BlockchainService;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\BlockTag;
use Baywall\Core\Domain\ValueObject\ChainId;

/**
 * クロール済みブロック番号を初期化します
 */
class InitCrawledBlockNumber {

	private const SAFETY_MARGIN_SECONDS = 60 * 5; // 5分

	public function __construct(
		private readonly CrawledBlockRepository $crawled_block_repository,
		private readonly ChainRepository $chain_repository,
		private readonly BlockchainService $blockchain_service
	) {}


	public function handle( int $chain_id_value ): void {
		$chain_id = ChainId::from( $chain_id_value );

		if ( null !== $this->crawled_block_repository->get( $chain_id ) ) {
			return; // 初期化済みの場合は何もしない
		}

		// 現在のブロック番号をクロール済みとして設定したいが、
		// RPC URLのプロバイダによって誤差が生じる可能性があるため
		// ある程度のマージンを持たせてクロール済みのブロック番号を設定する

		$safe_crawled_block_number = $this->getSafetyCrawledBlockNumber( $chain_id );
		$this->crawled_block_repository->save( $chain_id, $safe_crawled_block_number );
	}

	/**
	 * クロール済みブロックとして設定しても問題ないブロック番号を取得します
	 */
	private function getSafetyCrawledBlockNumber( ChainId $chain_id ): BlockNumber {
		$chain = $this->chain_repository->get( $chain_id );
		if ( null === $chain ) {
			throw new \InvalidArgumentException( "[9A5C802D] Chain with ID {$chain_id} does not exist." );
		}

		$rpc_url = $chain->rpcUrl();
		if ( null === $rpc_url ) {
			throw new \InvalidArgumentException( "[A67E0A9C] Chain {$chain->id()} does not have a valid RPC URL." );
		}

		// 最新のブロック情報を取得
		$res                 = $this->blockchain_service->getBlockByNumber( $rpc_url, BlockTag::latest() );
		$latest_block_number = $res->number();
		$latest_timestamp    = $res->timestamp();

		// 1000ブロック前の情報を取得
		$target_block_number = BlockNumber::fromInt( max( $latest_block_number->int() - 1000, 1 ) ); // マイナスにならないように調整
		$prev_res            = $this->blockchain_service->getBlockByNumber( $rpc_url, $target_block_number );
		$prev_block_number   = $prev_res->number();
		$prev_timestamp      = $prev_res->timestamp();

		// ブロックの平均生成時間を計算(ゼロ除算は発生し得ないためチェック不要)
		$average_block_time = ( $latest_timestamp->value() - $prev_timestamp->value() ) / ( $latest_block_number->int() - $prev_block_number->int() );

		// マージンを取ったブロック番号を計算
		$safety_block_number = BlockNumber::fromInt(
			// マイナスにならないように調整
			max( $latest_block_number->int() - (int) ceil( self::SAFETY_MARGIN_SECONDS / $average_block_time ), 1 )
		);

		// confirmationsを考慮したブロック番号を計算
		$confirmations       = $chain->confirmations();
		$safety_block_number = BlockNumber::fromInt(
			// マイナスにならないように調整
			max( $safety_block_number->int() - $confirmations->value(), 1 )
		);

		return $safety_block_number;
	}
}
