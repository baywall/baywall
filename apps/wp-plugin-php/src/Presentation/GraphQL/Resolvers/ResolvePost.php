<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetPost;
use Baywall\Core\Application\UseCase\GetPostTitle;
use Baywall\Core\Domain\ValueObject\PostId;

class ResolvePost implements Resolver {
	public function __construct(
		private readonly GetPost $get_post,
		private readonly GetPostTitle $get_post_title
	) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ) {
		$post = $this->get_post->handle( PostId::from( $args['postId'] ) );

		$post_title_callback = function () use ( $post ) {
			return $this->get_post_title->handle( $post->id() );
		};

		return array(
			'id'             => $post->id()->value(),
			'title'          => $post_title_callback,
			'sellingPrice'   => fn() => $root_value['sellingPrice']( $root_value, array( 'postId' => $post->id()->value() ) ),
			'sellingContent' => fn() => $root_value['sellingContent']( $root_value, array( 'postId' => $post->id()->value() ) ),
		);
	}
}
