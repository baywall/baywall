<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\Port\UserAccessProvider;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\Content;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Infrastructure\WordPress\Content\BlockNameProvider;
use Baywall\Core\Infrastructure\WordPress\Content\GutenbergService;
use Baywall\Core\Presentation\Hooks\Service\CurrentPaywalledPostResolver;

/**
 * 投稿取得時のフック
 *
 * @internal
 */
class ContentLoadHook {
	public function __construct(
		private readonly UserAccessProvider $user_access_provider,
		private readonly GutenbergService $gutenberg_service,
		private readonly PostRepository $post_repository,
		private readonly BlockNameProvider $block_name_provider,
		private readonly CurrentPaywalledPostResolver $current_paywalled_post_resolver
	) {}

	public function register(): void {
		// 投稿内容を取得する際のフィルタを登録
		// [通常の画面]
		add_filter( 'the_content', array( $this, 'theContentFilter' ), 10, 1 );
		// [リビジョン画面表示]
		add_filter( '_wp_post_revision_field_post_content', array( $this, 'wpPostRevisionFieldPostContentFilter' ), 10, 4 );
		// [APIレスポンス]
		// ※ Gutenbergでは`the_editor_content`が動作しないので`rest_prepare_post`(`rest_prepare_page`)を使用する
		// 　 https://github.com/WordPress/gutenberg/issues/12081#issuecomment-451631170
		add_filter( 'rest_prepare_post', array( $this, 'restPreparePostFilter' ), 10, 3 );
		add_filter( 'rest_prepare_page', array( $this, 'restPreparePageFilter' ), 10, 3 );
	}

	/**
	 * 投稿の内容をフィルタします。
	 * 投稿、固定ページの内容に有料記事のウィジェットを追加します。
	 */
	public function theContentFilter( string $content ): string {
		// 有料記事の情報がある場合はウィジェットを結合して返す
		// ※ $content はすでに `the_content` フィルタが適用された後の内容であることに注意
		// -> ダミーブロックはコメントだけなので削除済み
		$post = $this->current_paywalled_post_resolver->resolve();
		if ( $post === null ) {
			return $content;    // ウィジェットが描画されない場合はそのまま返す
		}

		// HTMLコメントを除去したウィジェットを追加
		return $content . $this->gutenberg_service->createWidgetBlock( $post->id() )->render();
	}

	/**
	 * リビジョン画面で表示される投稿内容をフィルタします。
	 * 差分は投稿全体で比較したいので、リビジョンの内容に有料記事のウィジェットと有料部分を追加します。
	 */
	public function wpPostRevisionFieldPostContentFilter( string $revision_field_content, string $field, \WP_Post $revision_post, string $context ): string {
		$revision_blocks           = $this->gutenberg_service->parseBlocks( Content::from( $revision_field_content ) );
		$dummy_paywall_block_index = $this->gutenberg_service->findBlockIndex( $revision_blocks, $this->block_name_provider->getDummyPaywallBlockName() );
		if ( $dummy_paywall_block_index === -1 ) {
			return $revision_field_content; // ダミーブロックが存在しない場合は何もしない
		}

		// ダミーブロックを除いた無料部分と有料部分から再組み立てして返す
		$post        = $this->post_repository->get( PostId::from( $revision_post->ID ) );
		$free_blocks = array_slice( $revision_blocks, 0, $dummy_paywall_block_index );
		return $this->gutenberg_service->buildContentWithPaidPart( $free_blocks, $post->id(), $post->paidContent() )->value();
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
		// ダミーブロックを除いた無料部分のブロック一覧
		$free_blocks = array_slice( $wp_post_blocks, 0, $dummy_paywall_block_index );

		// 投稿の編集権限がある場合はウィジェットと有料部分も含めて再組み立てする
		$paid_content = null;
		if ( $this->user_access_provider->canEditPost( $post_id ) ) {
			$paid_content = $this->post_repository->get( $post_id )->paidContent();
		}

		$raw_html = $this->gutenberg_service->buildContentWithPaidPart( $free_blocks, $post_id, $paid_content )->value();
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
