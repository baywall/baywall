<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\Service\PostTitleProvider;
use Baywall\Core\Domain\ValueObject\PostId;

class ResolvePost {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly PostRepository $post_repository,
		private readonly PostTitleProvider $post_title_provider
	) {}

	public function handle( array $root_value, array $args ) {
		$post_id = PostId::from( $args['postId'] );
		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 投稿の情報を取得
		$post = $this->post_repository->get( $post_id );

		$post_title_callback = function () use ( $post ) {
			return $this->post_title_provider->getPostTitle( $post->id() );
		};

		return array(
			'id'             => $post->id()->value(),
			'title'          => $post_title_callback,
			'sellingPrice'   => fn() => $root_value['sellingPrice']( $root_value, array( 'postId' => $post->id()->value() ) ),
			'sellingContent' => fn() => $root_value['sellingContent']( $root_value, array( 'postId' => $post->id()->value() ) ),
		);
	}
}
