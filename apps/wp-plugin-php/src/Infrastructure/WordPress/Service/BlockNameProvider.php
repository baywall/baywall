<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\BlockName;

class BlockNameProvider {

	/** ブロックエディタで使用されるブロック名を取得します。 */
	public function get(): BlockName {
		assert( file_exists( Config::BLOCK_JSON_PATH ), '[5F98FB07] block.json file not found.' );
		$block_json = file_get_contents( Config::BLOCK_JSON_PATH );
		$block      = json_decode( $block_json, true );
		$block_name = $block['name'];
		return BlockName::from( $block_name );
	}
}
