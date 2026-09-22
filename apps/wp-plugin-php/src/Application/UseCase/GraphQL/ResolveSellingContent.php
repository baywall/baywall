<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Service\PaidContentService;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\PostId;

class ResolveSellingContent {


	public function __construct(
		private readonly AppLogger $logger,
		private readonly PostRepository $post_repository,
		private readonly UserAccessChecker $user_access_checker,
		private readonly PaidContentService $paid_content_service
	) {}

	public function handle( array $root_value, array $args ) {
		$post_id = PostId::from( $args['postId'] );

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 有料部分のコンテンツを取得
		$paid_content = $this->post_repository->get( $post_id )->paidContent();

		// 有料部分のコンテンツが取得できなかった場合はnullを返す
		if ( null === $paid_content ) {
			$this->logger->warn( "[248F67EA] Paid content is null for post ID: {$post_id}" );
			return null;
		}

		return array(
			'characterCount' => $this->paid_content_service->getCharacterCount( $paid_content ),
			'imageCount'     => $this->paid_content_service->getImageCount( $paid_content ),
		);
	}
}
