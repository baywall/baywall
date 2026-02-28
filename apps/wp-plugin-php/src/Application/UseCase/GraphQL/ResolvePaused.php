<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Domain\Repository\PausedRepository;

class ResolvePaused {

	private PausedRepository $paused_repository;

	public function __construct( PausedRepository $paused_repository ) {
		$this->paused_repository = $paused_repository;
	}

	public function handle( array $root_value, array $args ): bool {
		// アクセス制御は不要
		return $this->paused_repository->get();
	}
}
