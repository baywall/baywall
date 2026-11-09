<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Domain\Entity\AppContract;
use Baywall\Core\Domain\Repository\AppContractRepository;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Infrastructure\WordPress\Database\Record\AppContractTableRecord;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\AppContractTable;

class WpAppContractRepository implements AppContractRepository {
	public function __construct(
		private readonly AppContractTable $app_contract_table,
		private readonly ChainRepository $chain_repository
	) {}

	/** @inheritdoc */
	public function get( ChainId $chain_id ): ?AppContract {
		$records = $this->app_contract_table->all();
		$records = array_filter(
			$records,
			fn( AppContractTableRecord $record ) => $record->chainIdValue() === $chain_id->value()
		);
		assert( count( $records ) <= 1, '[68E05B97] should return at most one record. - ' . count( $records ) );

		$record = array_values( $records )[0] ?? null;
		if ( null === $record ) {
			return null;
		}

		$chain = $this->chain_repository->get( $chain_id );
		if ( null === $chain ) {
			return null; // AppContract は Chain 非 null 必須のため、チェーン欠損時は契約なしとして扱う
		}

		return new AppContract(
			$chain,
			Address::from( $record->addressValue() )
		);
	}
}
