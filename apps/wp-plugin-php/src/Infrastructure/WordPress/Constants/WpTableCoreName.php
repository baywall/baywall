<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Constants;

final class WpTableCoreName {
	/** テーブル名に付与するプレフィックス */
	public const PREFIX = 'baywall_';

	/** Appコントラクト情報を格納するテーブル */
	public const APP_CONTRACT = self::PREFIX . 'app_contract';
	/** チェーンの情報を格納するテーブル */
	public const CHAIN = self::PREFIX . 'chain';
	/** クロール済みのブロックを格納するテーブル */
	public const CRAWLED_BLOCK = self::PREFIX . 'crawled_block';
	/** ERC-4361のNonceを格納するテーブル */
	public const ERC4361_NONCE = self::PREFIX . 'erc4361_nonce';
	/** 発行した請求書情報を格納するテーブル */
	public const INVOICE = self::PREFIX . 'invoice';
	/** 発行した請求書トークンを格納するテーブル */
	public const INVOICE_TOKEN = self::PREFIX . 'invoice_token';
	/** Oracleの定義を格納するテーブル */
	public const ORACLE = self::PREFIX . 'oracle';
	/** 有料コンテンツの情報を格納するテーブル */
	public const PAID_CONTENT = self::PREFIX . 'paid_content';
	/** リフレッシュトークンを格納するテーブル */
	public const REFRESH_TOKEN = self::PREFIX . 'refresh_token';
	/** 販売者の情報を格納するテーブル */
	public const SELLER = self::PREFIX . 'seller';
	/** サーバー署名者の情報を格納するテーブル */
	public const SERVER_SIGNER = self::PREFIX . 'server_signer';
	/** (支払時に使用する)トークンの情報を格納するテーブル */
	public const TOKEN = self::PREFIX . 'token';
	/** ペイウォール解除時のトランザクションに関するデータを格納するテーブル */
	public const UNLOCK_PAYWALL_TRANSACTION = self::PREFIX . 'unlock_paywall_transaction';
	/** ペイウォール解除時のトークン転送イベントの内容を格納するテーブル */
	public const UNLOCK_PAYWALL_TRANSFER_EVENT = self::PREFIX . 'unlock_paywall_transfer_event';
}
