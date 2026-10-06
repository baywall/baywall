<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Domain\Repository\ServerSignerRepository;

class ResolveServerSigner {


	public function __construct( private readonly ServerSignerRepository $server_signer_repository ) {}

	public function handle( array $root_value, array $args ) {

		$address = $this->server_signer_repository->getAddress()->value();

		return array(
			'address' => $address,
		);
	}
}
