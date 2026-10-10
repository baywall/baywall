<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks\Service;

use Baywall\Core\Domain\Entity\Post;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\PostId;

/**
 * 現在のリクエストでペイウォールウィジェットが描画される対象の投稿を解決するクラス。
 *
 * ウィジェット(`the_content`フィルタ)とviewスクリプトの出力条件を一致させるために使用する。
 */
class CurrentPaywalledPostResolver {

	public function __construct( private readonly PostRepository $post_repository ) {}

	/**
	 * ウィジェットが描画される対象の投稿を返します。
	 * 描画されない場合は`null`を返します。
	 */
	public function resolve(): ?Post {
		if ( ! is_single() && ! is_page() ) {
			return null;    // 投稿、固定ページ以外は対象外
		}

		$post_id = PostId::fromNullable( isset( $GLOBALS['post'] ) ? $GLOBALS['post']->ID : null );
		if ( $post_id === null ) {
			return null;    // 投稿IDが取得できない場合は対象外
		}

		// 有料記事の情報がある場合のみ対象
		$post = $this->post_repository->get( $post_id );
		if ( $post->paidContent() !== null ) {
			return $post;
		}
		return null;
	}
}
