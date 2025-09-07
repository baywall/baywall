<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

class I18nText {
	/**
	 * プラグイン名を取得します。
	 */
	public function pluginName(): string {
		return __( 'Qik Chain Pay', 'qik-chain-pay' );
	}

	/** メインネット */
	public function mainnet(): string {
		return __( 'Mainnet', 'qik-chain-pay' );
	}

	/** テストネット */
	public function testnet(): string {
		return __( 'Testnet', 'qik-chain-pay' );
	}

	/** プライベートネット */
	public function privatenet(): string {
		return __( 'Privatenet', 'qik-chain-pay' );
	}
}
