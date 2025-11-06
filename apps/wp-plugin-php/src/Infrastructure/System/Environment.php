<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\System;

use Cornix\Serendipity\Core\Constant\Config;

/**
 * インストールされている環境から情報を取得するクラス。
 * マシンに配置されているファイルやインストール済みのデータベースなど、実行環境によって異なる情報を取得する場合に使用します。
 */
class Environment {

	/** developmentモードを判定するためのディレクトリパス */
	private const DEVELOPMENT_MODE_CHECK_DIR = Config::ROOT_DIR . '/node_modules';

	/**
	 * 本番環境かどうかを取得します。
	 *
	 * 開発モードおよびテストモードでない場合にtrueを返します。
	 */
	public function isProduction(): bool {
		return ! $this->isDevelopment() && ! $this->isTesting();
	}

	/**
	 * 開発モードかどうかを取得します。
	 *
	 * 通常操作において、以下の状態の場合はtrueを返します。
	 * - localhost:8888等、開発用WordPressへアクセスしている時
	 *
	 * また、以下の状態の場合はfalseを返します。
	 * - 本番環境での運用時(zipファイルからインストールした場合)
	 * - テスト(phpunit)実行時
	 */
	public function isDevelopment(): bool {
		return ! $this->isTesting() && is_dir( self::DEVELOPMENT_MODE_CHECK_DIR );
	}

	/**
	 * PHPUnitのテスト中かどうかを取得します。
	 */
	public function isTesting(): bool {
		return 'testing' === getenv( 'APP_ENV' );
	}
}
