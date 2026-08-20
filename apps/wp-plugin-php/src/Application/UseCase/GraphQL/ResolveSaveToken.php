<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Infrastructure\Web3\Service\NativeTokenService;
use Baywall\Core\Infrastructure\Web3\Client\TokenClient;

/**
 * トークンの情報をサーバーに登録します。
 */
class ResolveSaveToken {

	private UserAccessChecker $user_access_checker;
	private TokenRepository $token_repository;
	private ChainRepository $chain_repository;
	private NativeTokenService $native_token_service;

	public function __construct(
		UserAccessChecker $user_access_checker,
		TokenRepository $token_repository,
		ChainRepository $chain_repository,
		NativeTokenService $native_token_service
	) {
		$this->user_access_checker  = $user_access_checker;
		$this->token_repository     = $token_repository;
		$this->chain_repository     = $chain_repository;
		$this->native_token_service = $native_token_service;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );
		$address  = Address::from( $args['address'] );
		/** @var bool */
		$is_payable = $args['isPayable'];

		$token = $this->token_repository->get( $chain_id, $address );
		if ( null === $token ) {
			// トークンデータが存在しない場合は新規登録を行うために少数点以下桁数とシンボルを取得する
			if ( $address->equals( Address::nativeToken() ) ) {
				// ネイティブトークンの場合
				$decimals = $this->native_token_service->getDecimals( $chain_id );
				$symbol   = $this->native_token_service->getSymbol( $chain_id );
			} else {
				// チェーンに接続してERC20コントラクトから少数点以下桁数とシンボルを取得する
				$chain        = $this->chain_repository->get( $chain_id );
				$token_client = new TokenClient( $chain->rpcUrl(), $address );

				$decimals = $token_client->decimals();
				$symbol   = $token_client->symbol();
			}
		} else {
			// decimals, symbolは保存されていた値を使用する
			$decimals = $token->decimals();
			$symbol   = $token->symbol();
		}

		// 小数点以下桁数が上限を超えるトークンは登録不可
		if ( $decimals->value() > Config::MAX_TOKEN_DECIMALS ) {
			throw new \InvalidArgumentException( '[01BD8D9A] Token decimals must be no greater than ' . Config::MAX_TOKEN_DECIMALS . ". decimals: {$decimals->value()}" );
		}

		// トークン情報を保存
		$token = new Token( $chain_id, $address, $symbol, $decimals, $is_payable );
		$this->token_repository->save( $token );

		return true;
	}
}
