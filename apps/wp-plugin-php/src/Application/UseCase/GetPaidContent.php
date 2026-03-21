<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\AccessTokenRequestProvider;
use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Application\Service\ConfirmationsService;
use Cornix\Serendipity\Core\Application\Service\SalesHistoryQueryService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\ValueObject\AccessToken;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\UnauthorizedException;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\SearchCondition\InvoiceSearchCondition;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class GetPaidContent {

	private AppLogger $logger;
	private UserAccessChecker $user_access_checker;
	private AccessTokenRequestProvider $access_token_request_provider;
	private AccessTokenService $access_token_service;
	private PostRepository $post_repository;
	private SalesHistoryQueryService $sales_history_query_service;
	private ChainRepository $chain_repository;
	private InvoiceRepository $invoice_repository;
	private ConfirmationsService $confirmations_service;
	private AppContractCrawlService $app_contract_crawl_service;

	public function __construct(
		AppLogger $logger,
		UserAccessChecker $user_access_checker,
		AccessTokenRequestProvider $access_token_request_provider,
		AccessTokenService $access_token_service,
		PostRepository $post_repository,
		SalesHistoryQueryService $sales_history_query_service,
		ChainRepository $chain_repository,
		InvoiceRepository $invoice_repository,
		ConfirmationsService $confirmations_service,
		AppContractCrawlService $app_contract_crawl_service
	) {
		$this->logger                        = $logger;
		$this->user_access_checker           = $user_access_checker;
		$this->access_token_request_provider = $access_token_request_provider;
		$this->access_token_service          = $access_token_service;
		$this->post_repository               = $post_repository;
		$this->sales_history_query_service   = $sales_history_query_service;
		$this->chain_repository              = $chain_repository;
		$this->invoice_repository            = $invoice_repository;
		$this->confirmations_service         = $confirmations_service;
		$this->app_contract_crawl_service    = $app_contract_crawl_service;
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

		// 販売履歴に記事の続きを要求したウォレットが指定した投稿を支払済みかどうかを取得
		$is_paid = $this->sales_history_query_service->existsByPostIdAndCustomerAddress( $post_id, $address );

		// 販売履歴に存在しない場合はブロックチェーンから情報を取得して確認する
		if ( ! $is_paid ) {
			$invoices = $this->invoice_repository->findBy( ( new InvoiceSearchCondition() )->setPostId( $post_id )->setCustomerAddress( $address ) );

			/** @var int[] チェック対象のチェーンID一覧 */
			$invoice_chain_id_values = array_reduce(
				$invoices,
				function ( array $carry, Invoice $invoice ) {
					$chain_id_value = $invoice->chainId()->value();
					if ( ! in_array( $chain_id_value, $carry, true ) ) {
						$carry[] = $chain_id_value;
					}
					return $carry;
				},
				array()
			);

			foreach ( $invoice_chain_id_values as $chain_id_value ) {
				$chain_id = ChainId::from( $chain_id_value );
				if ( ! $this->chain_repository->get( $chain_id )->connectable() ) {
					continue; // ブロックチェーンに接続できない場合はスキップ
				}

				$is_confirmed = $this->confirmations_service->isConfirmed( $chain_id, $post_id, $address );
				if ( $is_confirmed ) {
					try {
						// 販売履歴に存在しないが、ブロックチェーン上では支払が確認できている
						// という状態なので購入履歴を更新しておく
						$this->app_contract_crawl_service->crawl( $chain_id );
					} catch ( \Throwable $e ) {
						$this->logger->error( $e );
						// 再スローはせずに処理を続行する
					}

					$is_paid = true;
					break;
				}
			}
		}

		// 購入済みの場合は有料部分を返す。
		if ( $is_paid ) {
			return $this->post_repository->get( $post_id )->paidContent()->value();
		} else {
			// 購入済みでない場合はエラーをスロー
			throw new PaymentRequiredException( "[E868EFEF] is_paid is false : {$post_id}" );
		}
	}
}
