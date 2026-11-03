<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Entity\AppContract;
use Baywall\Core\Domain\Repository\AppContractRepository;
use Baywall\Core\Domain\ValueObject\ChainId;

class GetAppContract {
	public function __construct( private readonly AppContractRepository $app_contract_repository ) {}

	public function handle( ChainId $chain_id ): ?AppContract {
		// 権限チェック不要
		return $this->app_contract_repository->get( $chain_id );
	}
}
