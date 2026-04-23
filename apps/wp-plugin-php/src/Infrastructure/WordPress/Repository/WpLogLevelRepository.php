<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Repository;

use Cornix\Serendipity\Core\Infrastructure\Logging\LogLevelRepository;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;

class WpLogLevelRepository implements LogLevelRepository {
	public function __construct() {
	}

	/** @inheritdoc */
	public function get( LogCategory $category ): LogLevel {
		$log_level_name = get_option( $this->getOptionName( $category ), null );
		// 初期化時に設定されているためnullになることは無い
		assert( $log_level_name !== null, "[F6F7CFF9] Log level for category {$category->name()} is not set." );
		return LogLevel::from( $log_level_name );
	}

	/** @inheritdoc */
	public function set( LogCategory $category, LogLevel $level ): void {
		update_option( $this->getOptionName( $category ), $level->name() );
	}

	/** 指定されたログカテゴリのログレベルを削除します。 */
	public function deleteLogLevel( LogCategory $category ): void {
		delete_option( $this->getOptionName( $category ) );
	}

	private function getOptionName( LogCategory $category ): string {
		if ( $category->equals( LogCategory::app() ) ) {
			return WpOptionName::LOG_LEVEL_APP;
		} elseif ( $category->equals( LogCategory::audit() ) ) {
			return WpOptionName::LOG_LEVEL_AUDIT;
		} else {
			throw new \RuntimeException( "[CAF0CE2E] Invalid log category: {$category->name()}" );
		}
	}
}
