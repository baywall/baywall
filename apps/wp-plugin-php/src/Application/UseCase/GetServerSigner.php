<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Repository\ServerSignerRepository;
use Baywall\Core\Domain\ValueObject\Address;

class GetServerSigner {
	public function __construct( private readonly ServerSignerRepository $server_signer_repository ) {}

	public function handle(): Address {
		return $this->server_signer_repository->getAddress();
	}
}
