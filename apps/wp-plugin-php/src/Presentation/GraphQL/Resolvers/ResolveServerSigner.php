<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetServerSigner;

class ResolveServerSigner implements Resolver {
	public function __construct( private readonly GetServerSigner $get_server_signer ) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ) {

		return array(
			'address' => $this->get_server_signer->handle()->value(),
		);
	}
}
