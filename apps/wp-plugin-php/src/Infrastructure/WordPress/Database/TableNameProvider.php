<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpTableCoreName;
use wpdb;

class TableNameProvider {

	private string $prefix;

	public function __construct( wpdb $wpdb ) {
		$this->prefix = $wpdb->prefix;
	}

	/** 発行した請求書情報を記録するテーブル名 */
	public function invoice(): string {
		return $this->prefix . WpTableCoreName::INVOICE;
	}

	/** 発行した請求書トークンを記録するテーブル名 */
	public function invoiceToken(): string {
		return $this->prefix . WpTableCoreName::INVOICE_TOKEN;
	}

	/** ペイウォール解除時のトランザクションに関するデータを記録するテーブル名 */
	public function unlockPaywallTransaction(): string {
		return $this->prefix . WpTableCoreName::UNLOCK_PAYWALL_TRANSACTION;
	}

	/** ペイウォール解除時のトークン転送イベントの内容を記録するテーブル名 */
	public function unlockPaywallTransferEvent(): string {
		return $this->prefix . WpTableCoreName::UNLOCK_PAYWALL_TRANSFER_EVENT;
	}

	/** Appコントラクトの情報を記録するテーブル名 */
	public function appContract(): string {
		return $this->prefix . WpTableCoreName::APP_CONTRACT;
	}

	/** チェーンの情報を記録するテーブル名 */
	public function chain(): string {
		return $this->prefix . WpTableCoreName::CHAIN;
	}

	/** (支払時に使用する)トークンの情報を記録するテーブル名 */
	public function token(): string {
		return $this->prefix . WpTableCoreName::TOKEN;
	}

	/** Oracleの定義を記録するテーブル名 */
	public function oracle(): string {
		return $this->prefix . WpTableCoreName::ORACLE;
	}

	public function paidContent(): string {
		return $this->prefix . WpTableCoreName::PAID_CONTENT;
	}

	public function serverSigner(): string {
		return $this->prefix . WpTableCoreName::SERVER_SIGNER;
	}

	public function seller(): string {
		return $this->prefix . WpTableCoreName::SELLER;
	}

	/** クロール済みのブロックを記録するテーブル名 */
	public function crawledBlock(): string {
		return $this->prefix . WpTableCoreName::CRAWLED_BLOCK;
	}

	public function refreshToken(): string {
		return $this->prefix . WpTableCoreName::REFRESH_TOKEN;
	}

	public function erc4361Nonce(): string {
		return $this->prefix . WpTableCoreName::ERC4361_NONCE;
	}
}
