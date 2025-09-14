<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Name;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

class CronActionName {

	private static function getPrefix(): string {
		return ( new PrefixProvider() )->cronActionName();
	}

	/**
	 * Appコントラクトのクロール処理を行うCronアクション名を取得します。
	 */
	public static function appContractCrawl(): string {
		return self::getPrefix() . 'app_contract_crawl';
	}
}
