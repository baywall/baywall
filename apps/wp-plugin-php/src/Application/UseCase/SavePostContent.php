<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Entity\WidgetAttributes;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\PaidContent;
use Baywall\Core\Domain\ValueObject\PostId;

class SavePostContent {
	public function __construct( private readonly PostRepository $post_repository ) {}

	/**
	 * 有料部分を保存・削除する。
	 * 契約: $paid_content と $widget_attributes は揃って null（削除）か揃って非 null（保存）。
	 * null と非 null の混在は不正な組合せとして assert で排除する。
	 */
	public function handle( int $post_id_value, ?PaidContent $paid_content, ?WidgetAttributes $widget_attributes ): void {
		assert( ( null === $paid_content ) === ( null === $widget_attributes ), '[9FDC54F4] paid_content and widget_attributes must be both null or both non-null.' );

		$post = $this->post_repository->get( PostId::from( $post_id_value ) );
		if ( null === $paid_content || null === $widget_attributes ) {
			$post->deletePaidContent();
		} else {
			$post->setPaidContent( $paid_content, $widget_attributes->sellingNetworkCategoryId(), $widget_attributes->sellingAmount(), $widget_attributes->sellingSymbol() );
		}
		$this->post_repository->save( $post );
	}
}
