<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

class CronActionNameProvider {

	private string $prefix;

	public function __construct( PrefixProvider $prefix_provider ) {
		$this->prefix = $prefix_provider->cronActionName();
	}

	/**
	 * Appコントラクトのクロール処理を行うCronアクション名を取得します。
	 */
	public function appContractCrawl(): string {
		return $this->prefix . 'app_contract_crawl';
	}
}
