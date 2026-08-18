<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Logging;

use Baywall\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogLevel;

interface LogLevelRepository {
	/** 指定されたログカテゴリの現在のログレベルを取得します。 */
	public function get( LogCategory $category ): LogLevel;

	/** 指定されたログカテゴリのログレベルを設定します。 */
	public function set( LogCategory $category, LogLevel $level ): void;
}
