<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Post;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\PostId;

class GetPost {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly PostRepository $post_repository
	) {}

	public function handle( PostId $post_id ): Post {
		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 投稿の情報を取得
		return $this->post_repository->get( $post_id );
	}
}
