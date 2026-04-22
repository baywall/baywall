<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;

class V20260419_010_AddInstallOriginUrlOption extends MigrationBase {

	public function version(): string {
		return '0.0.2';
	}

	public function up(): void {
		update_option( WpOptionName::INSTALL_ORIGIN_URL, home_url() );
	}

	public function down(): void {
		delete_option( WpOptionName::INSTALL_ORIGIN_URL );
	}
}
