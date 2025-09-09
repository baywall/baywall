<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class ResolveSellingPrice {

	private AppLogger $logger;
	private PostRepository $post_repository;
	private UserAccessChecker $user_access_checker;

	public function __construct(
		AppLogger $logger,
		PostRepository $post_repository,
		UserAccessChecker $user_access_checker
	) {
		$this->logger              = $logger;
		$this->post_repository     = $post_repository;
		$this->user_access_checker = $user_access_checker;
	}

	public function handle( array $root_value, array $args ) {

		$post_id = PostId::from( $args['postId'] );

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 販売価格をテーブルから取得して返す
		$selling_price = $this->post_repository->get( $post_id )->sellingPrice();

		if ( is_null( $selling_price ) ) {
			$this->logger->warn( "[57B6E802] Selling price is null for post ID: {$post_id}" );
		}

		return is_null( $selling_price ) ? null : array(
			'amount' => $selling_price->amount()->value(),
			'symbol' => $selling_price->symbol()->value(),
		);
	}
}
