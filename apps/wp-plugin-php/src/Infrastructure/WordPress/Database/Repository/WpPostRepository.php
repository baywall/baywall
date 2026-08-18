<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Domain\ValueObject\PaidContent;
use Baywall\Core\Domain\Entity\Post;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Domain\ValueObject\Symbol;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\PaidContentTable;
use Baywall\Core\Infrastructure\WordPress\Database\ValueObject\PaidContentTableRecord;

class WpPostRepository implements PostRepository {

	public function __construct( PaidContentTable $paid_content_table ) {
		$this->paid_content_table = $paid_content_table;
	}

	private PaidContentTable $paid_content_table;

	/** @inheritdoc */
	public function get( PostId $post_id ): Post {
		if ( false === get_post_status( $post_id->value() ) ) {
			// 投稿が存在しない場合は例外を投げる
			throw new \InvalidArgumentException( "[7D8F3E0D] Post with ID {$post_id} does not exist." );
		}

		// テーブルから有料記事情報を取得
		$record = $this->paid_content_table->select( $post_id );

		return $record ? new PostImpl( $record ) : new Post( $post_id, null, null, null, null );
	}

	/** @inheritdoc */
	public function save( Post $post ): void {

		if ( null === $post->paidContent() ) {
			// 有料記事の内容がnullの場合は、テーブルから削除
			$this->paid_content_table->delete( $post->id() );
		} else {
			// 有料記事の内容がある場合は、テーブルに保存
			$this->paid_content_table->set(
				$post->id(),
				$post->paidContent(),
				$post->sellingNetworkCategoryId(),
				$post->sellingAmount(),
				$post->sellingSymbol(),
			);
		}
	}
}

/** @internal */
class PostImpl extends Post {

	public function __construct( PaidContentTableRecord $record ) {
		parent::__construct(
			PostId::from( $record->postIdValue() ),
			PaidContent::from( $record->paidContentValue() ),
			NetworkCategoryId::fromNullable( $record->sellingNetworkCategoryIdValue() ),
			Amount::fromNullable( $record->sellingAmountValue() ),
			Symbol::fromNullable( $record->sellingSymbolValue() ),
		);
	}
}
