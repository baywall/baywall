<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use InvalidArgumentException;

/**
 * チェーンの情報を取得するクラス
 */
class ChainService {
	public function __construct( ChainRepository $repository ) {
		$this->repository = $repository;
	}
	private ChainRepository $repository;

	/**
	 * リポジトリに登録されているチェーン一覧を取得します。
	 *
	 * @return Chain[]
	 * @deprecated Use ChainRepository::all
	 */
	public function getAllChains(): array {
		return $this->repository->all();
	}

	/**
	 *
	 * @param ChainId       $chain_id
	 * @param Confirmations $confirmations
	 * @deprecated Use ChainRepository::save
	 */
	public function saveConfirmations( ChainId $chain_id, Confirmations $confirmations ): void {
		$chain = $this->repository->get( $chain_id );
		if ( $chain === null ) {
			throw new InvalidArgumentException( "[86725830] Chain with ID {$chain_id} does not exist." );
		}
		$chain->setConfirmations( $confirmations );
		$this->repository->save( $chain );
	}
}
