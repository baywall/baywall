<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Service\AppContractDataProvider;
use Baywall\Core\Domain\Service\BlockchainService;
use Baywall\Core\Domain\Specification\ConfirmationsSpecification;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\PostId;

class ConfirmationsService {


	public function __construct(
		private readonly ChainRepository $chain_repository,
		private readonly AppContractDataProvider $app_contract_data_provider,
		private readonly BlockchainService $blockchain_service,
		private readonly ConfirmationsSpecification $confirmations_specification
	) {}

	/**
	 * ブロックチェーンに問い合わせて該当の投稿が購入され、指定待機ブロック経過しているかどうかを返します
	 *
	 * ※ 販売履歴のチェックは行いません
	 */
	public function isConfirmed( ChainId $chain_id, PostId $post_id, Address $buyer_address ): bool {
		$chain = $this->chain_repository->get( $chain_id );
		if ( null === $chain ) {
			throw new \LogicException( "[D20245FE] Chain with ID {$chain_id} does not exist." );
		}

		// 購入時のブロック番号をコントラクトから取得
		$unlocked_block_number = $this->app_contract_data_provider->unlockedBlockNumber( $chain_id, $post_id, $buyer_address );
		if ( $unlocked_block_number === null ) {
			return false; // コントラクトのストレージに解除済みの記録がない場合は支払いが確認できないとみなす
		}

		// 現在のブロック番号をブロックチェーンから取得
		$rpc_url = $chain->rpcUrl();
		if ( null === $rpc_url ) {
			throw new \InvalidArgumentException( "[6DD872FD] Chain {$chain_id} does not have a valid RPC URL." );
		}
		$current_block_number = $this->blockchain_service->getBlockNumber( $rpc_url );

		// ブロックが待機済みかどうかを判定して返す
		$confirmations = $chain->confirmations();
		return $this->confirmations_specification->isConfirmed( $unlocked_block_number, $current_block_number, $confirmations );
	}
}
