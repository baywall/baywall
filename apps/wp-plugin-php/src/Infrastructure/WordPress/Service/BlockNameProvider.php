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

	/**
	 * ペイウォール及び有料部分と差し替えるブロック名を取得します。
	 */
	public function getDummyPaywallBlockName(): BlockName {
		// 確実に他のプラグインと競合しないブロック名を指定。
		// この文字列は`wp_posts`テーブルの`post_content`列に書き込まれるため変更不可。
		// プラグイン名等が変更されたとしてもこの値は変更してはいけないので、ここでは直値で記述。
		return BlockName::from( 'ed8f6bb06b7a/b0fe5c007751' );
	}
}
