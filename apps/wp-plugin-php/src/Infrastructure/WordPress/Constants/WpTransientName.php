<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Constants;

final class WpTransientName {
	/** transient(optionsテーブルの一時データ)として格納する際のキー名に付与するプレフィックス */
	public const PREFIX = 'baywall_';

	/** app-manifest.jsonのJSON文字列を格納する際のキー名 */
	public const APP_MANIFEST = self::PREFIX . 'app_manifest';
}
