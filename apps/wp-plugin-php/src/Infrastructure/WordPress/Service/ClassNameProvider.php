<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\WpConfig;

class ClassNameProvider {

	/**
	 * ペイウォールブロック(ブロックエディタで表示されるウィジェット)に付与されるCSSクラス名を返します。
	 *
	 * @return string
	 */
	public function paywallBlock(): string {
		return WpConfig::PAYWALL_BLOCK_CLASS_NAME;
	}
}
