<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

class CronActionName {

	private string $prefix;

	public function __construct( PrefixProvider $prefixProvider ) {
		$this->prefix = $prefixProvider->cronActionName();
	}

	/**
	 * Appコントラクトのクロール処理を行うCronアクション名を取得します。
	 */
	public function appContractCrawl(): string {
		return $this->prefix . 'app_contract_crawl';
	}
}
