<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\Entity\Post;
use Baywall\Core\Domain\ValueObject\PostId;

interface PostRepository {

	/** 指定した投稿IDに合致する投稿情報を取得します。 */
	public function get( PostId $post_id ): Post;

	/** 指定された投稿IDのタイトルを取得します。投稿が存在しない場合はnullを返します。 */
	public function findTitle( PostId $post_id ): ?string;

	/** 投稿情報を保存します。 */
	public function save( Post $post ): void;
}
