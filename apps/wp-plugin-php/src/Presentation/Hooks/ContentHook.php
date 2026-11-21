<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

/**
 * 投稿内容を保存・取得する際のhooksを登録するクラス
 *
 * ※ サードパーティ製テーマやプラグインとの干渉が生じる可能性があるため、
 *    互換性の問題が判明した際は個別に対処する必要がある。
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
