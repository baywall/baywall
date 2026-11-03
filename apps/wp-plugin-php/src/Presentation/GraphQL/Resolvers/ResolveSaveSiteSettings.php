<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\SaveSiteSettings;
use Baywall\Core\Domain\ValueObject\SctaUrl;
use Baywall\Core\Domain\ValueObject\ThemeSetting;

class ResolveSaveSiteSettings implements Resolver {
	public function __construct(
		private readonly SaveSiteSettings $save_site_settings
	) {}

	public function handle( array $root_value, array $args ): bool {
		/** @var array{paused: bool, sctaUrl?: string|null, purgeOnUninstall: bool, themeSetting: string} */
		$input = $args['input'];

		$theme_setting = ThemeSetting::from( $input['themeSetting'] ); // 値の妥当性を検証
		$scta_url      = SctaUrl::fromNullable( $input['sctaUrl'] ?? null );

		return $this->save_site_settings->handle(
			$input['paused'],
			$scta_url,
			$input['purgeOnUninstall'],
			$theme_setting
		);
	}
}
