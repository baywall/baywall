<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Dto\SellingContentDto;
use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Port\PaidContentAnalyzer;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\PostId;

class GetSellingContent {
	public function __construct(
		private readonly AppLogger $logger,
		private readonly UserAccessChecker $user_access_checker,
		private readonly PostRepository $post_repository,
		private readonly PaidContentAnalyzer $paid_content_analyzer
	) {}

	public function handle( PostId $post_id ): ?SellingContentDto {
		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 有料部分のコンテンツを取得
		$paid_content = $this->post_repository->get( $post_id )->paidContent();

		// 有料部分のコンテンツが取得できなかった場合はnullを返す
		if ( null === $paid_content ) {
			$this->logger->warn( "[248F67EA] Paid content is null for post ID: {$post_id}" );
			return null;
		}

		return new SellingContentDto(
			$this->paid_content_analyzer->getCharacterCount( $paid_content ),
			$this->paid_content_analyzer->getImageCount( $paid_content )
		);
	}
}
