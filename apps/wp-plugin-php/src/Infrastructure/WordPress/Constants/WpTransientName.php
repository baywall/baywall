<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Constants;

final class WpTransientName {
	/** transient(optionsテーブルの一時データ)として格納する際のキー名に付与するプレフィックス */
	public const PREFIX = 'baywall_';
}
