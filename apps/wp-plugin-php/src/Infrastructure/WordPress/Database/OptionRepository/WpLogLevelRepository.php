<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\OptionRepository;

use Baywall\Core\Application\Logging\LogLevelRepository;
use Baywall\Core\Application\Logging\ValueObject\LogCategory;
use Baywall\Core\Application\Logging\ValueObject\LogLevel;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;
use Baywall\Core\Infrastructure\WordPress\Database\OptionGateway\Option\StringOption;

class WpLogLevelRepository implements LogLevelRepository {

	/** @inheritdoc */
	public function get( LogCategory $category ): LogLevel {
		$log_level_name = $this->option( $category )->get();
		// 初期化時に設定されているためnullになることは無い
		assert( $log_level_name !== null, "[F6F7CFF9] Log level for category {$category->name()} is not set." );
		return LogLevel::from( $log_level_name );
	}

	/** @inheritdoc */
	public function set( LogCategory $category, LogLevel $level ): void {
		$this->option( $category )->update( $level->name() );
	}

	/** 指定されたログカテゴリのログレベルを削除します。 */
	public function deleteLogLevel( LogCategory $category ): void {
		$this->option( $category )->delete();
	}

	private function option( LogCategory $category ): StringOption {
		return new StringOption( $this->getOptionName( $category ) );
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
