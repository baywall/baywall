<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Entity\Post;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class ResolvePost {

	private AppLogger $logger;
	private UserAccessChecker $user_access_checker;
	private PostRepository $post_repository;
	private ChainRepository $chain_repository;
	private TokenRepository $token_repository;
	private PostTitleProvider $post_title_provider;

	public function __construct(
		AppLogger $logger,
		UserAccessChecker $user_access_checker,
		PostRepository $post_repository,
		ChainRepository $chain_repository,
		TokenRepository $token_repository,
		PostTitleProvider $post_title_provider
	) {
		$this->logger              = $logger;
		$this->user_access_checker = $user_access_checker;
		$this->post_repository     = $post_repository;
		$this->chain_repository    = $chain_repository;
		$this->token_repository    = $token_repository;
		$this->post_title_provider = $post_title_provider;
	}

	public function handle( array $root_value, array $args ) {
		$post_id = PostId::from( $args['postId'] );
		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 投稿の情報を取得
		$post = $this->post_repository->get( $post_id );

		$payable_tokens_callback = function () use ( $root_value, $post ) {
			return array_map(
				fn( Token $token ) => $root_value['token'](
					$root_value,
					array(
						'chainId' => $token->chainId()->value(),
						'address' => $token->address()->value(),
					)
				),
				$this->getPayableTokens( $post )
			);
		};

		$post_title_callback = function () use ( $post ) {
			return $this->post_title_provider->getPostTitle( $post->id() );
		};

		return array(
			'id'             => $post->id()->value(),
			'title'          => $post_title_callback,
			'sellingPrice'   => fn() => $root_value['sellingPrice']( $root_value, array( 'postId' => $post->id()->value() ) ),
			'sellingContent' => fn() => $root_value['sellingContent']( $root_value, array( 'postId' => $post->id()->value() ) ),
			'payableTokens'  => $payable_tokens_callback,
		);
	}

	/** 指定した投稿で支払可能なトークン一覧を取得します */
	private function getPayableTokens( Post $post ) {
		$selling_network_category_id = $post->sellingNetworkCategoryId();

		if ( $selling_network_category_id === null ) {
			$this->logger->warn( '[22EB42D3] Selling network category is null for post ID: ' . $post->id()->value() );
			return array(); // 販売ネットワークカテゴリが設定されていない場合は空の配列を返す
		}

		// 投稿に設定されている販売ネットワークカテゴリかつ接続可能なチェーン一覧を取得
		$payable_chains = ( new ChainsFilter() )
			->byNetworkCategoryId( $selling_network_category_id )
			->byConnectable()
			->apply( $this->chain_repository->all() );

		/** @var Token[] */
		$result = array();

		// 各チェーンに対して支払可能なトークンを取得
		$all_tokens = $this->token_repository->all();
		foreach ( $payable_chains as $chain ) {
			$payable_tokens = ( new TokensFilter() )
				->byChainId( $chain->id() )
				->byIsPayable( true )
				->apply( $all_tokens );

			array_push( $result, ...$payable_tokens );
		}

		return $result;
	}
}
