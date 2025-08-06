<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\ChainTableRecord;

class ChainRepositoryImpl implements ChainRepository {

	public function __construct( ChainTable $chain_table ) {
		$this->chain_table = $chain_table;
	}

	private ChainTable $chain_table;

	/** @inheritdoc */
	public function get( ChainId $chain_id ): ?Chain {
		$filtered_chains = ( new ChainsFilter() )
			->byChainId( $chain_id )
			->apply( $this->all() );
		assert( count( $filtered_chains ) <= 1, '[BB8A90CF] should return at most one record.' );
		return empty( $filtered_chains ) ? null : array_values( $filtered_chains )[0];
	}

	/** @inheritdoc */
	public function all(): array {
		return array_map(
			fn( $record ) => new ChainImpl( $record ),
			$this->chain_table->all()
		);
	}

	/** @inheritdoc */
	public function save( Chain $chain ): void {
		$this->chain_table->save( $chain );
	}
}

/** @internal */
class ChainImpl extends Chain {
	public function __construct( ChainTableRecord $record ) {
		parent::__construct(
			ChainId::from( $record->chainIdValue() ),
			$record->nameValue(),
			NetworkCategoryId::from( $record->networkCategoryIdValue() ),
			RpcUrl::fromNullable( $record->rpcUrlValue() ),
			Confirmations::from( $record->confirmationsValue() ),
			$record->blockExplorerUrlValue()
		);
	}
}
