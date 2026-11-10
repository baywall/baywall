<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

/**
 * 投稿内容を保存、または取得時のhooksを登録するクラス
 * ※ インストールしているテーマやプラグインの影響を受ける可能性が高い。
 *    完全な対応は難しいため、問題があれば都度対応することになると思われる。
 */
class ContentHook implements Hook {
	public function __construct(
		private readonly ContentSaveHook $content_save_hook,
		private readonly ContentLoadHook $content_load_hook,
		private readonly ContentDeleteHook $content_delete_hook,
	) {}

	/**
	 * フックを登録します。
	 */
	public function register(): void {
		$this->content_save_hook->register();
		$this->content_load_hook->register();
		$this->content_delete_hook->register();
	}
}
