<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\WpConfig;

class ClassNameProvider {

	/**
	 * ブロック(ブロックエディタで表示されるウィジェット)に付与されるCSSクラス名を返します。
	 *
	 * @return string
	 */
	public function getBlock(): string {
		return WpConfig::PAYWALL_BLOCK_CLASS_NAME;
	}
}
