<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Domain\Repository\PausedRepository;

class ResolvePaused {


	public function __construct( private readonly PausedRepository $paused_repository ) {}

	public function handle( array $root_value, array $args ): bool {
		// アクセス制御は不要
		return $this->paused_repository->get();
	}
}
