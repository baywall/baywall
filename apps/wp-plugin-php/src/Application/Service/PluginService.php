<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

/** プラグインのサポート対象バージョンの判定を行うサービスを提供します */
interface PluginService {
	/** 指定したWordPressバージョンがサポート対象なら true */
	public function isSupportCmsVersion( string $cms_version ): bool;
	/** 指定したPHPバージョンがサポート対象なら true */
	public function isSupportPhpVersion( string $php_version ): bool;
}
