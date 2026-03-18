<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\AccessTokenRequestProvider;
use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\SalesHistoryQueryService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\ValueObject\AccessToken;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\UnauthorizedException;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class GetPaidContent {

	private UserAccessChecker $user_access_checker;
	private AccessTokenRequestProvider $access_token_request_provider;
	private AccessTokenService $access_token_service;
	private PostRepository $post_repository;
	private SalesHistoryQueryService $sales_history_query_service;

	public function __construct(
		UserAccessChecker $user_access_checker,
		AccessTokenRequestProvider $access_token_request_provider,
		AccessTokenService $access_token_service,
		PostRepository $post_repository,
		SalesHistoryQueryService $sales_history_query_service
	) {
		$this->user_access_checker           = $user_access_checker;
		$this->access_token_request_provider = $access_token_request_provider;
		$this->access_token_service          = $access_token_service;
		$this->post_repository               = $post_repository;
		$this->sales_history_query_service   = $sales_history_query_service;
	}

	public function handle( int $post_id_value ): string {
		$post_id = PostId::from( $post_id_value );
		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// アクセストークンをCookieから取得
		$access_token_value = $this->access_token_request_provider->get();
		if ( $access_token_value === null ) {
			throw new UnauthorizedException( '[8AD8257F] Access token is missing.' );
		}
		$access_token = AccessToken::from( $access_token_value );

		// アクセストークンの妥当性（有効期限切れ）チェック
		// ※ 署名が不正な場合はdecode時に例外がスローされる
		if ( ! $this->access_token_service->isValid( $access_token ) ) {
			throw new UnauthorizedException( '[3975F78C] Access token is invalid.' );
		}

		// アクセストークンの所有者（ウォレットアドレス）を取得
		$address = $this->access_token_service->getWalletAddress( $access_token );

		// 記事の続きを要求したウォレットが指定した投稿を支払済みかどうかを取得
		$is_paid = $this->sales_history_query_service->existsByPostIdAndCustomerAddress( $post_id, $address );

		// 購入済みの場合は有料部分を返す。
		if ( $is_paid ) {
			return $this->post_repository->get( $post_id )->paidContent()->value();
		} else {
			// 購入済みでない場合はエラーをスロー
			throw new PaymentRequiredException( "[E868EFEF] is_paid is false : {$post_id}" );
		}
	}
}
