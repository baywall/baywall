<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Application\UseCase\SavePostContent;
use Baywall\Core\Domain\Entity\WidgetAttributes;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\Content;
use Baywall\Core\Domain\ValueObject\PaidContent;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Infrastructure\WordPress\Content\BlockNameProvider;
use Baywall\Core\Infrastructure\WordPress\Content\GutenbergService;

/**
 * 投稿保存時のフック
 *
 * @internal
 */
class ContentSaveHook {

	/**
	 * 有料部分。保存直前のフィルタで導出され、`save_post`でのDB保存に使われる
	 */
	private ?PaidContent $pending_paid_content = null;
	/**
	 * 有料部分の販売設定。`pending_paid_content`と対で導出される
	 */
	private ?WidgetAttributes $pending_widget_attributes = null;
	/**
	 * 有料記事が削除されていたかどうかのフラグ。保存前に値がセットされ、保存後に評価される
	 */
	private bool $pending_delete_paid_content = false;

	public function __construct(
		private readonly GutenbergService $gutenberg_service,
		private readonly BlockNameProvider $block_name_provider,
		private readonly PostRepository $post_repository,
		private readonly SavePostContent $save_post_content
	) {}

	public function register(): void {
		// `wp_insert_post_data`は保存直前のhook。投稿IDが付与されていない可能性あり。
		// `save_post`は`wp_insert_post_data`で保持した投稿内容から有料記事の情報をテーブルに保存する。
		add_filter( 'wp_insert_post_data', array( $this, 'wpInsertPostDataFilter' ), 10, 2 );
		add_action( 'save_post', array( $this, 'savePostAction' ), 10, 2 );
	}

	/**
	 * 投稿の保存前のフック
	 *
	 * @param array<string, mixed> $data
	 * @param array<string, mixed> $postarr
	 * @return array<string, mixed>
	 */
	public function wpInsertPostDataFilter( array $data, array $postarr ): array {
		$is_revision_restoring = $postarr['post_type'] === 'post' && ( $_GET['action'] ?? null ) === 'restore' && is_numeric( $_GET['revision'] ?? null );

		$post_content = Content::from( wp_unslash( $data['post_content'] ?? '' ) );   // 加工前の投稿内容
		$blocks       = $this->gutenberg_service->parseBlocks( $post_content );

		$paywall_block_index       = $this->gutenberg_service->findBlockIndex( $blocks, $this->block_name_provider->get() );
		$dummy_paywall_block_index = $this->gutenberg_service->findBlockIndex( $blocks, $this->block_name_provider->getDummyPaywallBlockName() );

		// すでにダミーに置き換えられている時
		if ( $dummy_paywall_block_index !== -1 ) {
			if ( $is_revision_restoring ) {
				// リビジョンからの復元の場合は有料部分と販売設定をフィールドに保持
				// ※ リビジョンから復元した際にさらにリビジョンが追加されるが、その投稿IDは`save_post`フィルタ内でしか取得できないため
				// ここで直接上書きすることはできない。
				$revision_post         = $this->post_repository->get( PostId::from( (int) $_GET['revision'] ) );
				$revision_paid_content = $revision_post->paidContent();
				if ( null === $revision_paid_content ) {
					// 復元元リビジョンに有料部分が無いのは想定外のデータ状態
					throw new \LogicException( "[CB4E2910] Paid content is null for revision ID: {$revision_post->id()}" );
				}

				// 有料部分は復元元の保存文字列をそのまま保持し、販売設定は復元元のselling系フィールドから直接導出する
				$this->pending_paid_content      = $revision_paid_content;
				$this->pending_widget_attributes = WidgetAttributes::from(
					$revision_post->sellingNetworkCategoryId(),
					$revision_post->sellingAmount(),
					$revision_post->sellingSymbol(),
				);
			}
			// pendingは変更しない（ネストされるリビジョン保存の`save_post`に直前の保存の有料部分を
			// 引き継ぎ、リビジョンIDの行へ有料部分をコピー保存するため）
			return $data;
		}

		if ( $paywall_block_index !== -1 ) {
			// ペイウォールブロックが存在する場合は有料部分・販売設定を分離してフィールドに保持
			$split                           = $this->gutenberg_service->splitPaywallBlocks( $blocks, $paywall_block_index );
			$this->pending_paid_content      = $split->paidContent();
			$this->pending_widget_attributes = $split->widgetAttributes();

			// $data['post_content'] を「無料部分＋ダミーブロック」に差し替えて返す
			$data['post_content'] = $this->gutenberg_service->serializeBlocks( $split->replacementBlocks() )->value();
			return $data;
		} else {
			// ペイウォールブロックが存在しない場合は有料部分のデータを削除するフラグを立てる
			$this->pending_delete_paid_content = true;
			return $data;
		}
	}

	/**
	 * 投稿の保存後のフック。
	 */
	public function savePostAction( int $post_id_value, \WP_Post $_ ): void {
		if ( $this->pending_paid_content !== null ) {
			// 有料部分が保持されていた場合は有料部分を更新する
			$this->save_post_content->handle( $post_id_value, $this->pending_paid_content, $this->pending_widget_attributes );
		} elseif ( $this->pending_delete_paid_content ) {
			// 有料部分のデータを削除する場合
			$this->save_post_content->handle( $post_id_value, null, null );
		}
	}
}
