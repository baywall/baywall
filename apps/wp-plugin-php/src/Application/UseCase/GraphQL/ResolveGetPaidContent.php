<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\UseCase\GetPaidContent;

class ResolveGetPaidContent {

	private GetPaidContent $get_paid_content;

	public function __construct(
		GetPaidContent $get_paid_content
	) {
		$this->get_paid_content = $get_paid_content;
	}

	public function handle( array $root_value, array $args ) {
		$paid_content_value = $this->get_paid_content->handle( $args['invoiceId'] );

		return array(
			// TODO: WordPressのフィルタを使っているのでApplication層に置くのは不適切。
			// Presentation層に移動するか、Infrastructure層に移動する
			'paidContent' => apply_filters( 'the_content', $paid_content_value ),
		);
	}
}
