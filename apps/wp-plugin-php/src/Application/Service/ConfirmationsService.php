<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Service\AppContractDataProvider;
use Cornix\Serendipity\Core\Domain\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Domain\Specification\ConfirmationsSpecification;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class ConfirmationsService {

	private ChainRepository $chain_repository;
	private AppContractDataProvider $app_contract_data_provider;
	private BlockNumberProvider $block_number_provider;
	private ConfirmationsSpecification $confirmations_specification;

	public function __construct( ChainRepository $chain_repository, AppContractDataProvider $app_contract_data_provider, BlockNumberProvider $block_number_provider, ConfirmationsSpecification $confirmations_specification ) {
		$this->chain_repository            = $chain_repository;
		$this->app_contract_data_provider  = $app_contract_data_provider;
		$this->block_number_provider       = $block_number_provider;
		$this->confirmations_specification = $confirmations_specification;
	}

	/**
	 * ブロックチェーンに問い合わせて該当の投稿が購入され、指定待機ブロック経過しているかどうかを返します
	 *
	 * ※ 販売履歴のチェックは行いません
	 */
	public function isConfirmed( ChainId $chain_id, PostId $post_id, Address $customer_address ): bool {
		// 購入時のブロック番号をコントラクトから取得
		$unlocked_block_number = $this->app_contract_data_provider->unlockedBlockNumber( $chain_id, $post_id, $customer_address );
		if ( $unlocked_block_number === null ) {
			return false; // コントラクトのストレージに解除済みの記録がない場合は支払いが確認できないとみなす
		}

		// 現在のブロック番号をブロックチェーンから取得
		$current_block_number = $this->block_number_provider->getByChainId( $chain_id );

		// ブロックが待機済みかどうかを判定して返す
		$confirmations = $this->chain_repository->get( $chain_id )->confirmations();
		return $this->confirmations_specification->isConfirmed( $unlocked_block_number, $current_block_number, $confirmations );
	}
}
