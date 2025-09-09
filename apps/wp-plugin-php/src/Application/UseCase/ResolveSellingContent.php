<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\PaidContentService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class ResolveSellingContent {

	private AppLogger $logger;
	private PostRepository $post_repository;
	private UserAccessChecker $user_access_checker;
	private PaidContentService $paid_content_service;

	public function __construct(
		AppLogger $logger,
		PostRepository $post_repository,
		UserAccessChecker $user_access_checker,
		PaidContentService $paid_content_service
	) {
		$this->logger               = $logger;
		$this->post_repository      = $post_repository;
		$this->user_access_checker  = $user_access_checker;
		$this->paid_content_service = $paid_content_service;
	}

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
