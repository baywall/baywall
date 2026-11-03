<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

/** プラグインのサポート対象バージョンの判定を行うサービスを提供します */
interface PluginCompatibilityChecker {
	/** 指定したWordPressバージョンがサポート対象なら true */
	public function isSupportCmsVersion( string $cms_version ): bool;

	/** 指定したPHPバージョンがサポート対象なら true */
	public function isSupportPhpVersion( string $php_version ): bool;
}
