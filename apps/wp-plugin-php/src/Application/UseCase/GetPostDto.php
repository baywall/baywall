<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\PostDto;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class GetPostDto {
	private PostRepository $post_repository;
	private PostTitleProvider $post_title_provider;
	private InvoiceRepository $invoice_repository;

	public function __construct(
		PostRepository $post_repository,
		PostTitleProvider $post_title_provider,
		InvoiceRepository $invoice_repository
	) {
		$this->post_repository     = $post_repository;
		$this->post_title_provider = $post_title_provider;
		$this->invoice_repository  = $invoice_repository;
	}

	public function handle( int $post_id ): ?PostDto {
		$post = $this->post_repository->get( PostId::from( $post_id ) );
		if ( $post === null ) {
			return null;
		}
		return new PostDto( $post->id()->value(), $this->post_title_provider->getPostTitle( PostId::from( $post_id ) ) );
	}

	public function handleByInvoiceId( string $invoice_id_value ): ?PostDto {
		$invoice_id = InvoiceId::from( $invoice_id_value );
		$invoice    = $this->invoice_repository->get( $invoice_id );
		return $this->handle( $invoice->postId()->value() );
	}
}
