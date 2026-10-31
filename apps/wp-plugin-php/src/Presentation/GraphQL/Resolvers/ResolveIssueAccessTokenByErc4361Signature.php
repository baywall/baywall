<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\IssueAccessTokenByErc4361Signature;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Signature;

/** ERC-4361の署名を検証してアクセストークン(+リフレッシュトークン)を発行するクラス */
class ResolveIssueAccessTokenByErc4361Signature implements Resolver {


	public function __construct(
		private readonly IssueAccessTokenByErc4361Signature $issue_access_token_by_erc4361_signature
	) {}

	public function handle( array $root_value, array $args ) {
		$this->issue_access_token_by_erc4361_signature->handle(
			Address::from( $args['address'] ),
			ChainId::from( $args['chainId'] ),
			Signature::from( $args['signature'] )
		);

		return array(
			'success' => true,
		);
	}
}
