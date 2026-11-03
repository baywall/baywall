<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Domain\ValueObject\Price;

class GetSellingPrice {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly PostRepository $post_repository
	) {}

	public function handle( PostId $post_id ): ?Price {
		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 販売価格をテーブルから取得して返す
		$post = $this->post_repository->get( $post_id );

		return $post->sellingPrice();
	}
}
