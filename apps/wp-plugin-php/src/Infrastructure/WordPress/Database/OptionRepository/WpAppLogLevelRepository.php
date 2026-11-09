<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\OptionRepository;

use Baywall\Core\Application\Logging\AppLogLevelRepository;
use Baywall\Core\Application\Logging\ValueObject\LogLevel;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;
use Baywall\Core\Infrastructure\WordPress\Database\OptionGateway\Option\StringOption;

class WpAppLogLevelRepository implements AppLogLevelRepository {
	/** @inheritdoc */
	public function get(): LogLevel {
		$log_level_name = $this->option()->get();
		// 初期化時に設定されているためnullになることは無い
		assert( $log_level_name !== null, '[F6F7CFF9] Log level is not set.' );
		return LogLevel::from( $log_level_name );
	}

	/** @inheritdoc */
	public function set( LogLevel $level ): void {
		$this->option()->update( $level->name() );
	}

	/** アプリケーションログのログレベルを削除します。 */
	public function deleteLogLevel(): void {
		$this->option()->delete();
	}

	private function option(): StringOption {
		return new StringOption( WpOptionName::LOG_LEVEL_APP );
	}
}
