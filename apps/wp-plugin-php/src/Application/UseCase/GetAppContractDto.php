<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\AppContractDto;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

/** 指定したチェーンIDのAppコントラクト情報を取得します */
class GetAppContractDto {

	public function __construct( AppContractRepository $app_contract_repository ) {
		$this->app_contract_repository = $app_contract_repository;
	}

	private AppContractRepository $app_contract_repository;

	public function handle( int $chain_id ): ?AppContractDto {
		$app_contract = $this->app_contract_repository->get( ChainId::from( $chain_id ) );
		if ( $app_contract === null ) {
			return null;
		}
		return new AppContractDto( $app_contract->address()->value() );
	}
}
