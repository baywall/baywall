<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\PurgeOnUninstallRepository;
use Cornix\Serendipity\Core\Application\Repository\SctaUrlRepository;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\PausedRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\SctaUrl;
use Cornix\Serendipity\Core\Infrastructure\Util\Strings;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;

class ResolveSaveSiteSettings {

	private UserAccessChecker $user_access_checker;
	private PausedRepository $paused_repository;
	private SctaUrlRepository $scta_url_repository;
	private PurgeOnUninstallRepository $purge_on_uninstall_repository;
	private TransactionService $transaction_service;
	private WordPressPropertyProvider $wordpress_property_provider;

	public function __construct(
		UserAccessChecker $user_access_checker,
		PausedRepository $paused_repository,
		SctaUrlRepository $scta_url_repository,
		PurgeOnUninstallRepository $purge_on_uninstall_repository,
		TransactionService $transaction_service,
		WordPressPropertyProvider $wordpress_property_provider
	) {
		$this->user_access_checker           = $user_access_checker;
		$this->paused_repository             = $paused_repository;
		$this->scta_url_repository           = $scta_url_repository;
		$this->purge_on_uninstall_repository = $purge_on_uninstall_repository;
		$this->transaction_service           = $transaction_service;
		$this->wordpress_property_provider   = $wordpress_property_provider;
	}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var array{paused: bool, sctaUrl?: string|null, purgeOnUninstall: bool} */
		$input = $args['input'];

		/** @var bool */
		$paused = $input['paused'];

		/** @var bool */
		$purge_on_uninstall = $input['purgeOnUninstall'];

		/** @var string|null */
		$scta_url_value = $input['sctaUrl'] ?? null;
		if ( $scta_url_value !== null ) {
			$home_url = $this->wordpress_property_provider->homeUrl();
			if ( ! Strings::starts_with( $scta_url_value, $home_url ) ) {
				throw new \InvalidArgumentException( '[57280AEF] sctaUrl must start with homeUrl.' );
			}
		}
		$scta_url = SctaUrl::fromNullable( $scta_url_value );

		return $this->transaction_service->transactional(
			function () use ( $paused, $scta_url, $purge_on_uninstall ) {
				$this->paused_repository->save( $paused );
				$this->scta_url_repository->save( $scta_url );
				$this->purge_on_uninstall_repository->save( $purge_on_uninstall );

				return true;
			}
		);
	}
}
