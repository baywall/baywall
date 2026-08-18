<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Application\Repository\Erc4361NonceRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Application\ValueObject\Erc4361Nonce;
use Baywall\Core\Application\ValueObject\Erc4361NonceString;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\Erc4361NonceTable;

class WpErc4361NonceRepository implements Erc4361NonceRepository {

	private Erc4361NonceTable $erc4361_nonce_table;

	public function __construct( Erc4361NonceTable $erc4361_nonce_table ) {
		$this->erc4361_nonce_table = $erc4361_nonce_table;
	}

	/** @inheritDoc */
	public function get( Address $address ): ?Erc4361Nonce {
		$record = $this->erc4361_nonce_table->get( $address );
		return $record !== null ? Erc4361Nonce::from(
			Erc4361NonceString::from( $record->erc4361NonceValue() ),
			UnixTimestamp::fromMySql( $record->issuedAtValue() )
		) : null;
	}

	/** @inheritDoc */
	public function save( Address $address, Erc4361Nonce $nonce ): void {
		$this->erc4361_nonce_table->save(
			$address,
			$nonce->nonce(),
			$nonce->issuedAt()
		);
	}

	/** @inheritDoc */
	public function delete( Address $address ): void {
		$this->erc4361_nonce_table->delete( $address );
	}

	/** @inheritDoc */
	public function deleteExpired( UnixTimestamp $target_time ): void {
		$this->erc4361_nonce_table->deleteExpired( $target_time );
	}
}
