<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Port\NativeTokenProvider;
use Baywall\Core\Application\Port\TokenClientFactory;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class SaveToken {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly ChainRepository $chain_repository,
		private readonly TokenRepository $token_repository,
		private readonly NativeTokenProvider $native_token_provider,
		private readonly TokenClientFactory $token_client_factory
	) {}

	/** トークンの情報をサーバーに登録します */
	public function handle( ChainId $chain_id, Address $address, bool $is_payable ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain = $this->chain_repository->get( $chain_id );
		if ( null === $chain ) {
			throw new \InvalidArgumentException( "[13BC3420] Chain with ID {$chain_id} does not exist." );
		}

		$token = $this->token_repository->get( $chain_id, $address );
		if ( null !== $token ) {
			// トークンデータが存在する場合は is_payable を更新
			$token->setIsPayable( $is_payable );

			// isPayable: false への変更は decimals 上限超過トークンの救済経路なので、支払い可能にする場合のみ上限チェックする
			if ( $is_payable ) {
				$this->checkDecimalsWithinLimit( $token );
			}
		} else {
			// トークンデータが存在しない場合は新規登録を行うために少数点以下桁数とシンボルを取得する
			if ( $address->equals( Address::nativeToken() ) ) {
				// ネイティブトークンの場合
				$decimals = $this->native_token_provider->getDecimals( $chain_id );
				$symbol   = $this->native_token_provider->getSymbol( $chain_id );
			} else {
				// チェーンに接続してERC20コントラクトから少数点以下桁数とシンボルを取得する
				$rpc_url = $chain->rpcUrl();
				if ( null === $rpc_url ) {
					throw new \InvalidArgumentException( "[1731FE8C] Chain {$chain_id} does not have a valid RPC URL." );
				}
				$token_client = $this->token_client_factory->create( $rpc_url, $address );

				$decimals = $token_client->decimals();
				$symbol   = $token_client->symbol();
			}

			// トークン情報を新規作成
			$token = new Token( $chain_id, $address, $symbol, $decimals, $is_payable );
			$this->checkDecimalsWithinLimit( $token );
		}

		// トークン情報の新規登録または更新
		$this->token_repository->save( $token );

		return true;
	}

	/**
	 * トークンの小数点以下桁数が上限を超えていないか確認します。
	 * 超過している場合は例外をスローします。
	 *
	 * @throws \InvalidArgumentException 上限を超える場合
	 */
	private function checkDecimalsWithinLimit( Token $token ): void {
		if ( $token->decimals()->value() > Config::MAX_TOKEN_DECIMALS ) {
			throw new \InvalidArgumentException( '[01BD8D9A] Token decimals must be no greater than ' . Config::MAX_TOKEN_DECIMALS . ". decimals: {$token->decimals()->value()}" );
		}
	}
}
