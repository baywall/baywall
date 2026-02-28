<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;

class ResolveServerSigner {

	private ServerSignerRepository $server_signer_repository;

	public function __construct( ServerSignerRepository $server_signer_repository ) {
		$this->server_signer_repository = $server_signer_repository;
	}

	public function handle( array $root_value, array $args ) {

		$server_signer = $this->server_signer_repository->get();

		return $server_signer === null ? null : array(
			'address' => $server_signer->address()->value(),
		);
	}
}
