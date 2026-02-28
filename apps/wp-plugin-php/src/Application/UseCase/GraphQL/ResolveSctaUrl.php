<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\SctaUrlRepository;

class ResolveSctaUrl {

	private SctaUrlRepository $scta_url_repository;

	public function __construct( SctaUrlRepository $scta_url_repository ) {
		$this->scta_url_repository = $scta_url_repository;
	}

	public function handle( array $root_value, array $args ): ?string {
		// アクセス制御は不要
		$scta_url = $this->scta_url_repository->get();
		return $scta_url === null ? null : $scta_url->value();
	}
}
