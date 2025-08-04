<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Repository\Name\Prefix;

class PrefixProvider {

	// TODO: Prefixクラスから処理をこのクラスへ移動

	public function optionKey(): string {
		return ( new Prefix() )->optionKeyPrefix();
	}


	public function tableName(): string {
		return ( new Prefix() )->tableNamePrefix();
	}

	public function transientKey(): string {
		return ( new Prefix() )->transientKeyPrefix();
	}
}
