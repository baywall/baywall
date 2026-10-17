<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Service\Clock;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Post;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\Service\InvoiceService;
use Baywall\Core\Domain\Service\PriceExchangeService;
use Baywall\Core\Domain\Specification\ChainsSpecification;
use Baywall\Core\Domain\Specification\TokensSpecification;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;

class ResolvePayableTokens {


	public function __construct(
		private readonly AppLogger $logger,
		private readonly UserAccessChecker $user_access_checker,
		private readonly PostRepository $post_repository,
		private readonly ChainRepository $chain_repository,
		private readonly TokenRepository $token_repository,
		private readonly PriceExchangeService $price_exchange_service,
		private readonly InvoiceService $invoice_service,
		private readonly Clock $clock
	) {}

	/** 支払いに使用可能なトークン一覧を取得します【ゲスト】 */
	public function handle( array $root_value, array $args ) {
		$now     = $this->clock->now();
		$post_id = PostId::from( $args['args']['postId'] );

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 投稿の情報を取得
		$post = $this->post_repository->get( $post_id );

		// 購入者アドレスが指定されていれば、直近に発行された請求書のチェーンへトークン一覧を限定する
		$buyer_address_value = $args['args']['buyerAddress'] ?? null;
		$buyer_address       = $buyer_address_value === null ? null : Address::from( $buyer_address_value );

		$tokens = $this->getPayableTokens( $post, $buyer_address, $now );

		return array_map(
			fn( Token $token ) => $root_value['token'](
				$root_value,
				array(
					'chainId' => $token->chainId()->value(),
					'address' => $token->address()->value(),
				)
			),
			$tokens
		);
	}

	/** 指定した投稿で支払可能なトークン一覧を取得します */
	private function getPayableTokens( Post $post, ?Address $buyer_address, UnixTimestamp $now ) {
		$selling_network_category_id = $post->sellingNetworkCategoryId();

		if ( $selling_network_category_id === null ) {
			$this->logger->warn( '[22EB42D3] Selling network category is null for post ID: ' . $post->id()->value() );
			return array(); // 販売ネットワークカテゴリが設定されていない場合は空の配列を返す
		}

		// 投稿に設定されている販売ネットワークカテゴリかつ接続可能なチェーン一覧を取得
		$payable_chains = array_filter(
			$this->chain_repository->all(),
			( new ChainsSpecification( network_category_id: $selling_network_category_id, is_connectable: true ) )->isSatisfiedBy( ... )
		);

		/** @var Token[] */
		$result = array();

		// 各チェーンに対して支払可能なトークンを取得
		$all_tokens = $this->token_repository->all();
		foreach ( $payable_chains as $chain ) {
			// 支払可能と設定されているトークンを抽出
			$payable_tokens = array_filter(
				$all_tokens,
				( new TokensSpecification( chain_id: $chain->id(), is_payable: true ) )->isSatisfiedBy( ... )
			);

			// そのうち、レート変換が可能なトークンのみを残す
			$payable_tokens = array_filter(
				$payable_tokens,
				fn( Token $token ) => $this->price_exchange_service->exchangeable(
					$post->sellingPrice(),
					$token->symbol()
				)
			);

			array_push( $result, ...$payable_tokens );
		}

		if ( $buyer_address === null ) {
			return $result;
		}

		return $this->filterByRecentInvoiceChain( $result, $post->id(), $buyer_address, $now );
	}

	/**
	 * 直近に発行された請求書のチェーン以外のトークンを除外します。
	 *
	 * 二重購入抑止（InvoiceService::isDuplicatePurchase）と同じ判定で、
	 * 別チェーンの請求書が窓内にあるチェーンのトークンを選択肢から外す。
	 */
	private function filterByRecentInvoiceChain( array $tokens, PostId $post_id, Address $buyer_address, UnixTimestamp $now ): array {
		// 判定は chainId 単位で共通するため、ユニークな chainId ごとに一度だけ評価する
		$unique_chain_ids = array();
		foreach ( $tokens as $token ) {
			$unique_chain_ids[ $token->chainId()->value() ] = $token->chainId();
		}

		$blocked_chain_ids = array();
		foreach ( $unique_chain_ids as $chain_id ) {
			if ( $this->invoice_service->isDuplicatePurchase( $post_id, $buyer_address, $chain_id, $now ) ) {
				$blocked_chain_ids[ $chain_id->value() ] = true;
			}
		}

		return array_values(
			array_filter(
				$tokens,
				fn( Token $token ) => ! isset( $blocked_chain_ids[ $token->chainId()->value() ] )
			)
		);
	}
}
