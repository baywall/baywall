<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Logging\WpLogLevelProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use wpdb;


class OptionsTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->serverSigner() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', OptionsTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class OptionsTableSeed_0_0_1 extends MigrationBase {

	public function __construct( WpLogLevelProvider $log_level_provider ) {
		$this->log_level_provider = $log_level_provider;
	}
	private WpLogLevelProvider $log_level_provider;

	public function up(): void {
		$is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$this->log_level_provider->setLogLevel( LogCategory::app(), $is_debug ? LogLevel::debug() : LogLevel::info() );
		$this->log_level_provider->setLogLevel( LogCategory::audit(), LogLevel::info() );
	}

	public function down(): void {
		$this->log_level_provider->deleteLogLevel( LogCategory::app() );
		$this->log_level_provider->deleteLogLevel( LogCategory::audit() );
	}
}
