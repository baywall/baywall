<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\UseCase\SavePostContent;

/**
 * 投稿削除時のフック
 *
 * @internal
 */
class ContentDeleteHook {
	public function __construct( private readonly SavePostContent $save_post_content ) {}

	public function register(): void {
		// 投稿が削除された時のフックを登録
		add_action( 'delete_post', array( $this, 'deletePostAction' ), 10, 1 );
	}

	/**
	 * 投稿が削除された時のアクション。有料記事の情報も削除します。
	 */
	public function deletePostAction( int $post_id ): void {
		// プラグイン更新時のzipアップロード処理では、一時ファイルがattachmentとして作成・削除され、
		// その削除でも`delete_post`が発火する。attachmentは有料記事の対象外なので処理しない。
		if ( get_post_type( $post_id ) === 'attachment' ) {
			return;
		}

		$this->save_post_content->handle( $post_id, null, null );
	}
}
