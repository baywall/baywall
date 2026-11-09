<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Content;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;

class HandleNameProvider {
	/** @return non-empty-string */
	public function blockScript(): string {
		return WpConfig::HANDLE_NAME_BLOCK_SCRIPT;
	}

	/** @return non-empty-string */
	public function adminScript(): string {
		return WpConfig::HANDLE_NAME_ADMIN_SCRIPT;
	}

	/** @return non-empty-string */
	public function viewScript(): string {
		return WpConfig::HANDLE_NAME_VIEW_SCRIPT;
	}

	/** @return non-empty-string */
	public function viewHostStyle(): string {
		return WpConfig::HANDLE_NAME_VIEW_HOST_STYLE;
	}
}
