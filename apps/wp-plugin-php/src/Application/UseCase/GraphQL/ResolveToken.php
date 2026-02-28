<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

class ResolveToken {

	private UserAccessChecker $user_access_checker;
	private TokenRepository $token_repository;

	public function __construct( UserAccessChecker $user_access_checker, TokenRepository $token_repository ) {
		$this->user_access_checker = $user_access_checker;
		$this->token_repository    = $token_repository;
	}

	public function handle( array $root_value, array $args ) {
		$chain_id = ChainId::from( $args['chainId'] );
		$address  = Address::from( $args['address'] );

		$token = $this->token_repository->get( $chain_id, $address );
		if ( $token === null ) {
			throw new \InvalidArgumentException( "[436EAC2D] Token not found for chain ID {$chain_id} and address {$address}" );
		}

		$decimals_callback = function () use ( $token ) {
			$this->user_access_checker->checkCanCreatePost(); // 投稿編集者の権限が必要
			return $token->decimals()->value();
		};

		return array(
			'chain'     => fn() => $root_value['chain']( $root_value, array( 'chainId' => $token->chainId()->value() ) ),
			'address'   => $token->address()->value(),
			'symbol'    => $token->symbol()->value(),
			'decimals'  => $decimals_callback,
			'isPayable' => $token->isPayable(),
		);
	}
}
