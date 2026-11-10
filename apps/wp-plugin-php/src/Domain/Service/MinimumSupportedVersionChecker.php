<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

interface MinimumSupportedVersionChecker {
	/**
	 * 現在のプラグインバージョンが最低サポートバージョン（下限）未満かどうかを返します。
	 * - true: 下限未満
	 * - false: 下限以上
	 * - null: 取得不可（manifest取得・RPC解決・バージョン取得等の失敗。例外を投げずnullを返します）
	 */
	public function isBelowMinimumSupportedVersion(): ?bool;
}
