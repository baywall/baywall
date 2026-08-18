<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\ValueObject\PostId;

interface PostTitleProvider {
	/** 指定された投稿IDのタイトルを取得します。 */
	public function getPostTitle( PostId $post_id ): ?string;
}
