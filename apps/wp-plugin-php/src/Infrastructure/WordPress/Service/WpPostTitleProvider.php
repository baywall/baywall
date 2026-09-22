<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Domain\Service\PostTitleProvider;
use Baywall\Core\Domain\ValueObject\PostId;

class WpPostTitleProvider implements PostTitleProvider {

	/** @inheritdoc */
	public function getPostTitle( PostId $post_id ): ?string {
		return get_post( $post_id->value() )?->post_title;
	}
}
