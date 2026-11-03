<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\OracleRepository;
use Baywall\Core\Domain\Specification\OraclesSpecification;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class GetOracle {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly OracleRepository $oracle_repository
	) {}

	public function handle( ChainId $chain_id, Address $address ): Oracle {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$specification = new OraclesSpecification( chain_id: $chain_id, address: $address );

		$filtered_oracles = array_filter( $this->oracle_repository->all(), $specification->isSatisfiedBy( ... ) );

		// チェーンIDとアドレスを指定しているので $filtered_oracles の長さは必ず1
		if ( 1 !== count( $filtered_oracles ) ) {
			throw new \RuntimeException( "[83F9617E] oracle data is not found or duplicated. chain id: {$chain_id}, address: {$address}, count: " . count( $filtered_oracles ) );
		}
		return array_values( $filtered_oracles )[0];
	}
}
