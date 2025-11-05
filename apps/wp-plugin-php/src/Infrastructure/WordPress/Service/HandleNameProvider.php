<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\WpConfig;

class HandleNameProvider {

	public function blockScript(): string {
		return WpConfig::HANDLE_NAME_BLOCK_SCRIPT;
	}

	public function adminScript(): string {
		return WpConfig::HANDLE_NAME_ADMIN_SCRIPT;
	}

	public function viewScript(): string {
		return WpConfig::HANDLE_NAME_VIEW_SCRIPT;
	}
}
