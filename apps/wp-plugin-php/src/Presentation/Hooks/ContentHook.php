<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Service\UserAccessProvider;
use Cornix\Serendipity\Core\Domain\Entity\WidgetAttributes;
use Cornix\Serendipity\Core\Domain\ValueObject\PaidContent;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Content;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\PaidContentTable;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\BlockNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\GutenbergService;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use DI\Container;
use WP_Block;
use WP_Post;

/**
 * 投稿内容を保存、または取得時のhooksを登録するクラス
 * ※ インストールしているテーマやプラグインの影響を受ける可能性が高い。
 *    完全な対応は難しいため、問題があれば都度対応することになると思われる。
 */
class ContentHook extends HookBase {

	private ContentSaveHook $content_save_hook;
	private ContentLoadHook $content_load_hook;
	private ContentDeleteHook $content_delete_hook;

	public function __construct( Container $container ) {
		$this->content_save_hook   = $container->get( ContentSaveHook::class );
		$this->content_load_hook   = $container->get( ContentLoadHook::class );
		$this->content_delete_hook = $container->get( ContentDeleteHook::class );
	}

	/**
	 * フックを登録します。
	 */
	public function register(): void {
		$this->content_save_hook->register();
		$this->content_load_hook->register();
		$this->content_delete_hook->register();
	}
}

/**
 * 投稿保存時のフック
 *
 * @internal
 */
class ContentSaveHook {
	private GutenbergService $gutenberg_service;
	private BlockNameProvider $block_name_provider;
	private PostRepository $post_repository;

	/**
	 * ペイウォールブロック＋有料部分のブロック一覧
	 *
	 * @var WP_Block[]|null
	 */
	private $pending_blocks = null;
	/** 有料記事が削除されていたかどうかのフラグ。保存前に値がセットされ、保存後に評価される */
	private $pending_delete_paid_content = false;

	public function __construct( GutenbergService $gutenberg_service, BlockNameProvider $block_name_provider, PostRepository $post_repository ) {
		$this->gutenberg_service   = $gutenberg_service;
		$this->block_name_provider = $block_name_provider;
		$this->post_repository     = $post_repository;
	}

	public function register(): void {
		// `wp_insert_post_data`は保存直前のhook。投稿IDが付与されていない可能性あり。
		// `save_post`は`wp_insert_post_data`で保持した投稿内容から有料記事の情報をテーブルに保存する。
		add_filter( 'wp_insert_post_data', array( $this, 'wpInsertPostDataFilter' ), 10, 2 );
		add_filter( 'save_post', array( $this, 'savePostFilter' ), 10, 2 );
	}

	/**
	 * 投稿の保存前のフック
	 */
	public function wpInsertPostDataFilter( array $data, array $postarr ): array {
		$is_revision_restoring = $postarr['post_type'] === 'post' && ( $_GET['action'] ?? null ) === 'restore' && is_numeric( $_GET['revision'] ?? null );

		// // 投稿IDを取得(新規作成時はnull)
		// $post_id_value= $postarr["ID"] ?? null;
		// $post_id = PostId::fromNullableValue( );
		$post_content = Content::from( wp_unslash( $data['post_content'] ?? '' ) );   // 加工前の投稿内容
		$blocks       = $this->gutenberg_service->parseBlocks( $post_content );

		$paywall_block_index       = $this->gutenberg_service->findBlockIndex( $blocks, $this->block_name_provider->get() );
		$dummy_paywall_block_index = $this->gutenberg_service->findBlockIndex( $blocks, $this->block_name_provider->getDummyPaywallBlockName() );

		// すでにダミーに置き換えられている時
		if ( $dummy_paywall_block_index !== -1 ) {
			if ( $is_revision_restoring ) {
				// リビジョンからの復元の場合はペイウォールブロック＋有料部分をフィールドに保持
				// ※ リビジョンから復元した際にさらにリビジョンが追加されるが、その投稿IDは`save_post`フィルタ内でしか取得できないため
				// ここで直接上書きすることはできない。
				$revision_post_id = PostId::from( (int) $_GET['revision'] );
				$revision_post    = $this->post_repository->get( $revision_post_id );
				assert( $revision_post->paidContent() !== null, '[B581B4FD]' );

				$pending_blocks               = array();
				$pending_blocks[]             = $this->gutenberg_service->createWidgetBlock(
					WidgetAttributes::from(
						$revision_post->sellingNetworkCategoryId(),
						$revision_post->sellingAmount(),
						$revision_post->sellingSymbol(),
					)
				);
				$revision_paid_content_blocks = $this->gutenberg_service->parseBlocks( Content::from( $revision_post->paidContent()->value() ) );

				$pending_blocks       = array_merge( $pending_blocks, $revision_paid_content_blocks );
				$this->pending_blocks = $pending_blocks;
			}
			return $data;
		}

		if ( $paywall_block_index !== -1 ) {
			// ペイウォールブロックが存在する場合はペイウォールブロック＋有料部分のブロック一覧をフィールドに保持
			$this->pending_blocks = array_slice( $blocks, $paywall_block_index );
			// 有料部分のブロック一覧のハッシュ値を取得
			$hash = wp_hash( $this->gutenberg_service->serializeBlocks( $this->pending_blocks ) );

			// 無料部分のブロック一覧を取得
			$free_content_blocks = array_slice( $blocks, 0, $paywall_block_index );
			// wp_postsに保存するブロックを保持する変数
			$wp_post_blocks   = $free_content_blocks;
			$wp_post_blocks[] = $this->createDummyPaywallBlock( $hash );

			// $data['post_content'] の値を更新して返す
			$data['post_content'] = $this->gutenberg_service->serializeBlocks( $wp_post_blocks )->value();
			return $data;
		} else {
			// ペイウォールブロックが存在しない場合は有料部分のデータを削除するフラグを立てる
			$this->pending_delete_paid_content = true;
			return $data;
		}
	}

	private function createDummyPaywallBlock( string $hash_value ): WP_Block {
		$block_name = $this->block_name_provider->getDummyPaywallBlockName();
		return new WP_Block(
			array(
				'blockName'    => $block_name->value(),
				'attrs'        => array( 'hash' => $hash_value ),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array( '' ),
			),
		);
	}

	/**
	 * 投稿の保存後のフック。
	 */
	public function savePostFilter( int $post_id_value, \WP_Post $_ ): void {
		$post = $this->post_repository->get( PostId::from( $post_id_value ) );
		if ( $this->pending_blocks !== null ) {
			$paywall_block_index = $this->gutenberg_service->findBlockIndex( $this->pending_blocks, $this->block_name_provider->get() );
			assert( $paywall_block_index === 0, '[6CD60862]' );
			// ペイウォールブロックが配置されていた場合は有料部分を更新する

			$widget_attributes = $this->gutenberg_service->getWidgetAttributes( $this->pending_blocks[ $paywall_block_index ] );
			$paid_content      = PaidContent::from( $this->gutenberg_service->serializeBlocks( array_slice( $this->pending_blocks, $paywall_block_index + 1 ) )->value() );
			$post->setPaidContent( $paid_content, $widget_attributes->sellingNetworkCategoryId(), $widget_attributes->sellingAmount(), $widget_attributes->sellingSymbol() );
			$this->post_repository->save( $post );
		} elseif ( $this->pending_delete_paid_content ) {
			// 有料部分のデータを削除する場合
			$post->deletePaidContent();
			$this->post_repository->save( $post );
		}
	}
}

/**
 * 投稿取得時のフック
 *
 * @internal
 */
class ContentLoadHook {

	private UserAccessProvider $user_access_provider;
	private GutenbergService $gutenberg_service;
	private PostRepository $post_repository;
	private BlockNameProvider $block_name_provider;

	public function __construct(
		UserAccessProvider $user_access_provider,
		GutenbergService $gutenberg_service,
		PostRepository $post_repository,
		BlockNameProvider $block_name_provider
	) {
		$this->user_access_provider = $user_access_provider;
		$this->gutenberg_service    = $gutenberg_service;
		$this->post_repository      = $post_repository;
		$this->block_name_provider  = $block_name_provider;
	}

	public function register(): void {
		// 投稿内容を取得する際のフィルタを登録
		// [通常の画面]
		add_filter( 'the_content', array( $this, 'theContentFilter' ), 10, 1 );
		// [リビジョン画面表示]
		add_filter( '_wp_post_revision_field_post_content', array( $this, 'wpPostRevisionFieldPostContentFilter' ), 10, 4 );
		// [APIレスポンス]
		// ※ Gutenbergでは`the_editor_content`が動作しないので`rest_prepare_post`(`rest_prepare_page`)を使用する
		// 　 https://github.com/WordPress/gutenberg/issues/12081#issuecomment-451631170
		// add_filter ( 'the_editor_content', array( $this, 'theEditorContentFilter' ), 10, 2 );
		add_filter( 'rest_prepare_post', array( $this, 'restPreparePostFilter' ), 10, 3 );
		add_filter( 'rest_prepare_page', array( $this, 'restPreparePageFilter' ), 10, 3 );
	}

	/**
	 * 投稿の内容をフィルタします。
	 * 投稿、固定ページの内容に有料記事のウィジェットを追加します。
	 */
	public function theContentFilter( string $content ): string {
		if ( ! is_single() && ! is_page() ) {
			return $content;    // 投稿、固定ページ以外は処理抜け
		}

		$post_id = PostId::fromNullableValue( isset( $GLOBALS['post'] ) ? $GLOBALS['post']->ID : null );
		if ( $post_id === null ) {
			return $content;    // 投稿IDが取得できない場合は処理抜け
		}

		// 有料記事の情報がある場合はウィジェットを結合して返す
		// ※ $content はすでに `the_content` フィルタが適用された後の内容であることに注意
		// -> ダミーブロックはコメントだけなので削除済み
		$post = $this->post_repository->get( $post_id );
		if ( $post->paidContent() !== null ) {
			$paywall_block = $this->gutenberg_service->createWidgetBlock(
				WidgetAttributes::from(
					$post->sellingNetworkCategoryId(),
					$post->sellingAmount(),
					$post->sellingSymbol(),
				)
			);
			// HTMLコメントを除去したウィジェットを追加
			return $content . $paywall_block->render();
		} else {
			return $content;
		}
	}

	/**
	 * リビジョン画面で表示される投稿内容をフィルタします。
	 * 差分は投稿全体で比較したいので、リビジョンの内容に有料記事のウィジェットと有料部分を追加します。
	 */
	public function wpPostRevisionFieldPostContentFilter( string $revision_field_content, string $field, \WP_Post $revision_post, string $context ) {
		$revision_blocks           = $this->gutenberg_service->parseBlocks( Content::from( $revision_field_content ) );
		$dummy_paywall_block_index = $this->gutenberg_service->findBlockIndex( $revision_blocks, $this->block_name_provider->getDummyPaywallBlockName() );
		if ( $dummy_paywall_block_index === -1 ) {
			return $revision_field_content; // ダミーブロックが存在しない場合は何もしない
		}

		$post_id             = PostId::from( $revision_post->ID );
		$post                = $this->post_repository->get( $post_id );
		$paid_content        = $post->paidContent();
		$paid_content_blocks = $paid_content ? $this->gutenberg_service->parseBlocks( Content::from( $paid_content->value() ) ) : array();

		// クライアントに返すブロック一覧を作成(この時点ではダミーブロックを除いた無料部分のブロック一覧)
		$result_blocks = array_slice( $revision_blocks, 0, $dummy_paywall_block_index );
		// ペイウォールブロックを追加
		$result_blocks[] = $this->gutenberg_service->createWidgetBlock(
			WidgetAttributes::from(
				$post->sellingNetworkCategoryId(),
				$post->sellingAmount(),
				$post->sellingSymbol()
			)
		);
		// 有料部分のブロックを追加
		$result_blocks = array_merge( $result_blocks, $paid_content_blocks );

		// シリアライズして返す
		return $this->gutenberg_service->serializeBlocks( $result_blocks )->value();
	}

	/**
	 * 投稿のREST APIレスポンスを加工します。
	 * このメソッドが呼び出されるタイミング:
	 *   - 投稿編集画面を開いたとき
	 *   - 投稿を保存した時
	 *   - wp/v2/posts等のAPIにアクセスした時
	 */
	public function restPreparePostFilter( \WP_REST_Response $response, \WP_Post $wp_post, \WP_REST_Request $request ): \WP_REST_Response {

		$wp_post_blocks            = $this->gutenberg_service->parseBlocks( Content::from( $wp_post->post_content ) );
		$dummy_paywall_block_index = $this->gutenberg_service->findBlockIndex( $wp_post_blocks, $this->block_name_provider->getDummyPaywallBlockName() );
		if ( $dummy_paywall_block_index === -1 ) {
			return $response;   // ダミーブロックが存在しない場合は何もしない
		}

		$data    = $response->get_data();
		$post_id = PostId::from( $wp_post->ID );
		// クライアントに返すブロック一覧を作成(この時点ではダミーブロックを除いた無料部分のブロック一覧)
		$result_blocks = array_slice( $wp_post_blocks, 0, $dummy_paywall_block_index );

		if ( $this->user_access_provider->canEditPost( $post_id ) ) {
			// 投稿の編集権限がある場合はペイウォールブロックと有料部分のブロックを追加

			$post          = $this->post_repository->get( $post_id );
			$paywall_block = $this->gutenberg_service->createWidgetBlock(
				WidgetAttributes::from(
					$post->sellingNetworkCategoryId(),
					$post->sellingAmount(),
					$post->sellingSymbol()
				)
			);

			// ペイウォールブロックを追加
			$result_blocks[] = $paywall_block;
			// 有料部分のブロックを追加
			$paid_content_blocks = $this->gutenberg_service->parseBlocks( Content::from( $post->paidContent() ? $post->paidContent()->value() : '' ) );
			$result_blocks       = array_merge( $result_blocks, $paid_content_blocks );
		}

		$raw_html = $this->gutenberg_service->serializeBlocks( $result_blocks )->value();
		// レスポンスの内容を加工
		$data['content']['rendered'] = apply_filters( 'the_content', $raw_html );
		if ( isset( $data['content']['raw'] ) ) {
			$data['content']['raw'] = $raw_html; // 投稿編集画面ではrawフィールドも加工
		}

		$response->set_data( $data );
		return $response;
	}
	public function restPreparePageFilter( \WP_REST_Response $response, \WP_Post $post, \WP_REST_Request $request ): \WP_REST_Response {
		// ひとまず、ページも投稿と同じ処理を適用しておく
		// 個別の処理が必要な場合はここを変更して対応する
		return $this->restPreparePostFilter( $response, $post, $request );
	}
}

/**
 * 投稿削除時のフック
 *
 * @internal
 */
class ContentDeleteHook {

	private PostRepository $post_repository;
	private PaidContentTable $paid_content_table;

	public function __construct(
		PostRepository $post_repository,
		PaidContentTable $paid_content_table
	) {
		$this->post_repository    = $post_repository;
		$this->paid_content_table = $paid_content_table;
	}

	public function register(): void {
		// 投稿が削除された時のフックを登録
		add_action( 'delete_post', array( $this, 'deletePostAction' ), 10, 1 );
	}

	/**
	 * 投稿が削除された時のアクション。有料記事の情報も削除します。
	 */
	public function deletePostAction( int $post_id ): void {
		// テーブルが存在する時のみ削除(PHPUnit動作時のエラー回避)
		if ( $this->paid_content_table->exists() ) {
			$post = $this->post_repository->get( PostId::from( $post_id ) );
			$post->deletePaidContent();
			$this->post_repository->save( $post );
		}
	}
}
