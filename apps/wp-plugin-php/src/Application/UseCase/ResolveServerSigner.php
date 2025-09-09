<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\ServerSignerService;

class ResolveServerSigner {

	private ServerSignerService $server_signer_service;

	public function __construct( ServerSignerService $server_signer_service ) {
		$this->server_signer_service = $server_signer_service;
	}

	public function handle( array $root_value, array $args ) {

		$server_signer = $this->server_signer_service->getServerSigner();

		return $server_signer === null ? null : array(
			'address' => $server_signer->address()->value(),
		);
	}
}
