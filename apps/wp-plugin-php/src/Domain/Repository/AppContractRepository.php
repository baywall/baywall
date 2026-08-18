<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\Entity\AppContract;
use Baywall\Core\Domain\ValueObject\ChainId;

interface AppContractRepository {

	/** Appコントラクトの情報を取得します */
	public function get( ChainId $chain_id ): ?AppContract;

	/** Appコントラクトの情報を保存します */
	public function save( AppContract $app_contract ): void;
}
