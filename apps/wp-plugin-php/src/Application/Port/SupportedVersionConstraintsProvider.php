<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

/**
 * サポート対象バージョンのComposer制約文字列を提供するインタフェース
 */
interface SupportedVersionConstraintsProvider {
	/**
	 * WordPressバージョンの対応制約を取得します。
	 *
	 * @return string[] Composer制約文字列の配列
	 */
	public function cmsVersionConstraints(): array;

	/**
	 * PHPバージョンの対応制約を取得します。
	 *
	 * @return string[] Composer制約文字列の配列
	 */
	public function phpVersionConstraints(): array;
}
