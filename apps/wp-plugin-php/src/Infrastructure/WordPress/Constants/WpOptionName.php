<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Constants;

final class WpOptionName {
	/** optionsテーブルに格納する際のキー名に付与するプレフィックス */
	public const PREFIX = 'baywall_';

	/** ログレベル（アプリケーションログ） */
	public const LOG_LEVEL_APP = self::PREFIX . 'log_level_app';
	/** ログレベル（監査ログ） */
	public const LOG_LEVEL_AUDIT = self::PREFIX . 'log_level_audit';

	/** インストール済みプラグインバージョン（プラグイン更新時に使用） */
	public const PLUGIN_VERSION = self::PREFIX . 'plugin_version';
	/** JWT秘密鍵 */
	public const JWT_SECRET_KEY = self::PREFIX . 'jwt_secret_key';
	/** 特定商取引法に基づく表記のURL */
	public const SCTA_URL = self::PREFIX . 'scta_url';
	/** 一時停止状態 */
	public const PAUSED = self::PREFIX . 'paused';
	/** インストール時のサイトURL */
	public const INSTALL_ORIGIN_URL = self::PREFIX . 'install_origin_url';
}
