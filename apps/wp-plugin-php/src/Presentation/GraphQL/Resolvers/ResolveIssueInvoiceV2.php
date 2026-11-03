<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\IssueInvoice;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\PostId;

/** 支払情報を発行(v2)する GraphQL リゾルバ */
class ResolveIssueInvoiceV2 implements Resolver {
	public function __construct(
		private readonly IssueInvoice $issue_invoice
	) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ) {
		return $this->issue_invoice->handle(
			PostId::from( $args['postId'] ),
			ChainId::from( $args['chainId'] ),
			Address::from( $args['tokenAddress'] ),
			Address::from( $args['buyerAddress'] )
		);
	}
}
