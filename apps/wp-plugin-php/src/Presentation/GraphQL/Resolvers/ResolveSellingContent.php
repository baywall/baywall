<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetSellingContent;
use Baywall\Core\Domain\ValueObject\PostId;

class ResolveSellingContent implements Resolver {
	public function __construct(
		private readonly GetSellingContent $get_selling_content
	) {}

	/** @return array<string, mixed>|null */
	public function handle( array $root_value, array $args ) {
		$selling_content = $this->get_selling_content->handle( PostId::from( $args['postId'] ) );

		if ( null === $selling_content ) {
			return null;
		}

		return array(
			'characterCount' => $selling_content->character_count,
			'imageCount'     => $selling_content->image_count,
		);
	}
}
