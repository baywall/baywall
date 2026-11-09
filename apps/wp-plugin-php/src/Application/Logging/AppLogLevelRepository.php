<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Logging;

use Baywall\Core\Application\Logging\ValueObject\LogLevel;

interface AppLogLevelRepository {
	/** 現在のアプリケーションログのログレベルを取得します。 */
	public function get(): LogLevel;

	/** アプリケーションログのログレベルを設定します。 */
	public function set( LogLevel $level ): void;
}
