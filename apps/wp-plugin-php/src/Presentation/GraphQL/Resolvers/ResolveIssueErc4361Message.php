<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\IssueErc4361Message;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveIssueErc4361Message implements Resolver {
	public function __construct(
		private readonly IssueErc4361Message $issue_erc4361_message
	) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ) {
		$message = $this->issue_erc4361_message->handle(
			Address::from( $args['address'] ),
			ChainId::from( $args['chainId'] )
		);

		return array(
			'message' => $message->value(),
		);
	}
}
