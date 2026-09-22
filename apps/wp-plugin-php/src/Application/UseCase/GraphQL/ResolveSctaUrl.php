<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Repository\SctaUrlRepository;

class ResolveSctaUrl {


	public function __construct( private readonly SctaUrlRepository $scta_url_repository ) {}

	public function handle( array $root_value, array $args ): ?string {
		// アクセス制御は不要
		$scta_url = $this->scta_url_repository->get();
		return $scta_url?->value();
	}
}
