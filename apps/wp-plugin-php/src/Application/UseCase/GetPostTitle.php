<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\PostId;

class GetPostTitle {
	public function __construct( private readonly PostRepository $post_repository ) {}

	public function handle( PostId $post_id ): ?string {
		return $this->post_repository->findTitle( $post_id );
	}
}
