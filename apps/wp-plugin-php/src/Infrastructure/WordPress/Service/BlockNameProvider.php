<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpConfig;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\BlockName;

class BlockNameProvider {

	/** ブロックエディタで使用されるブロック名を取得します。 */
	public function get(): BlockName {
		if ( file_exists( WpConfig::BLOCK_JSON_PATH ) ) {
			$block_json = file_get_contents( WpConfig::BLOCK_JSON_PATH );
			$block      = json_decode( $block_json, true );
			$block_name = $block['name'];
			assert( $block_name === WpConfig::PAYWALL_BLOCK_NAME, '[5F98FB07] block.json file does not match the expected block name.' );
		}
		return BlockName::from( WpConfig::PAYWALL_BLOCK_NAME );
	}

	/**
	 * ペイウォール及び有料部分と差し替えるブロック名を取得します。
	 */
	public function getDummyPaywallBlockName(): BlockName {
		return BlockName::from( WpConfig::PAYWALL_BLOCK_DUMMY_NAME );
	}
}
