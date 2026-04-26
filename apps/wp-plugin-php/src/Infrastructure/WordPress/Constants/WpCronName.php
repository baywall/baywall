<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Constants;

final class WpCronName {
	/** Cronジョブ名に付与するプレフィックス */
	public const PREFIX = 'baywall_';

	/** Appコントラクトのクロール処理を行うCronアクション名 */
	public const APP_CONTRACT_CRAWL = self::PREFIX . 'app_contract_crawl';
}
