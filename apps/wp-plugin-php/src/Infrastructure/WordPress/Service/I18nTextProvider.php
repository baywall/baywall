<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

class I18nTextProvider {
	/**
	 * プラグイン名を取得します。
	 */
	public function pluginName(): string {
		return __( 'baywall', 'baywall' );
	}

	/** メインネット */
	public function mainnet(): string {
		return __( 'Mainnet', 'baywall' );
	}

	/** テストネット */
	public function testnet(): string {
		return __( 'Testnet', 'baywall' );
	}

	/** 設定 */
	public function settings(): string {
		// 設定はデフォルトのテキストドメインを使用（第二引数の指定なし）
		return __( 'Settings' );
	}

	/** プライベートネット */
	public function privatenet(): string {
		return __( 'Privatenet', 'baywall' );
	}
}
