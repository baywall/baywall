<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;

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
