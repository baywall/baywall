<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\Config;

class BlockNameProvider {

	/** ブロックエディタで使用されるブロック名を取得します。 */
	public function get(): string {
		$block_json = file_get_contents( Config::ROOT_DIR . '/build/block/block.json' );
		$block      = json_decode( $block_json, true );
		$block_name = $block['name'];
		return $block_name;
	}
}
