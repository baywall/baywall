<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class ResolveSellingPrice {

	private PostRepository $post_repository;
	private UserAccessChecker $user_access_checker;

	public function __construct(
		PostRepository $post_repository,
		UserAccessChecker $user_access_checker
	) {
		$this->post_repository     = $post_repository;
		$this->user_access_checker = $user_access_checker;
	}

	public function handle( array $root_value, array $args ) {

		$post_id = PostId::from( $args['postId'] );

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 販売価格をテーブルから取得して返す
		$post = $this->post_repository->get( $post_id );

		return $post->sellingPrice() === null ? null : array(
			'amount' => $post->sellingAmount()->value(),
			'symbol' => $post->sellingSymbol()->value(),
		);
	}
}
