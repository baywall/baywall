<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\PaidContent;
use Cornix\Serendipity\Core\Domain\Entity\Post;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\PaidContentTable;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\PaidContentTableRecord;

class PostRepositoryImpl implements PostRepository {

	public function __construct( ?PaidContentTable $paid_content_table = null ) {
		$this->paid_content_table = $paid_content_table ?? new PaidContentTable( $GLOBALS['wpdb'] );
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

		return $record ? new PostImpl( $record ) : new Post( $post_id, null, null, null );
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
				$post->sellingNetworkCategoryID(),
				$post->sellingPrice()
			);
		}
	}
}

/** @internal */
class PostImpl extends Post {

	public function __construct( PaidContentTableRecord $record ) {
		parent::__construct(
			new PostId( $record->postIdValue() ),
			PaidContent::from( $record->paidContentValue() ),
			NetworkCategoryID::from( $record->sellingNetworkCategoryIdValue() ),
			$this->getPriceFromRecord( $record ),
		);
	}

	private function getPriceFromRecord( PaidContentTableRecord $record ): ?Price {
		$selling_amount_value = $record->sellingAmountValue();
		$selling_symbol       = $record->sellingSymbolValue();
		if ( null === $selling_amount_value || null === $selling_symbol ) {
			return null;
		} else {
			return new Price( Amount::from( $selling_amount_value ), new Symbol( $selling_symbol ) );
		}
	}
}
