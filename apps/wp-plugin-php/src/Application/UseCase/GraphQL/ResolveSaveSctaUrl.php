<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\SctaUrlRepository;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\ValueObject\SctaUrl;

class ResolveSaveSctaUrl {

	private UserAccessChecker $user_access_checker;
	private SctaUrlRepository $scta_url_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		SctaUrlRepository $scta_url_repository
	) {
		$this->user_access_checker = $user_access_checker;
		$this->scta_url_repository = $scta_url_repository;
	}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var string|null */
		$scta_url_value = $args['sctaUrl'] ?? null;
		$scta_url       = SctaUrl::fromNullable( $scta_url_value );

		$this->scta_url_repository->save( $scta_url );

		return true;
	}
}
