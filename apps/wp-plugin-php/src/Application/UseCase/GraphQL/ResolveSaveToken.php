<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Web3\TokenClient;

/**
 * トークンの情報をサーバーに登録します。
 */
class ResolveSaveToken {

	private UserAccessChecker $user_access_checker;
	private TokenRepository $token_repository;
	private ChainRepository $chain_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		TokenRepository $token_repository,
		ChainRepository $chain_repository
	) {
		$this->user_access_checker = $user_access_checker;
		$this->token_repository    = $token_repository;
		$this->chain_repository    = $chain_repository;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );
		$address  = Address::from( $args['address'] );
		/** @var bool */
		$is_payable = $args['isPayable'];

		$token = $this->token_repository->get( $chain_id, $address );
		if ( null === $token ) {
			// ネイティブトークンはプラグインインストール時に登録済みのため、ここを通る場合は例外を投げる
			if ( $address->equals( Ethers::zeroAddress() ) ) {
				throw new \RuntimeException( "[7B2CA023] Native token is already registered. chain ID: {$chain_id}" );
			}

			// トークンデータが存在しない場合は新規登録を行うために少数点以下桁数とシンボルを取得する

			// チェーンに接続してERC20コントラクトから少数点以下桁数とシンボルを取得する
			$chain        = $this->chain_repository->get( $chain_id );
			$token_client = new TokenClient( $chain->rpcUrl(), $address );

			$decimals = $token_client->decimals();
			$symbol   = $token_client->symbol();
		} else {
			// decimals, symbolは保存されていた値を使用する
			$decimals = $token->decimals();
			$symbol   = $token->symbol();
		}

		// トークン情報を保存
		$token = new Token( $chain_id, $address, $symbol, $decimals, $is_payable );
		$this->token_repository->save( $token );

		return true;
	}
}
