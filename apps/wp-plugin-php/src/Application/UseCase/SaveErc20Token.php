<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\TokenClient;

class SaveErc20Token {
	public function __construct( TokenRepository $token_repository, ChainRepository $chain_repository ) {
		$this->token_repository = $token_repository;
		$this->chain_repository = $chain_repository;
	}

	private TokenRepository $token_repository;
	private ChainRepository $chain_repository;

	public function handle( ChainId $chain_id, Address $address, bool $is_payable ): void {
		$token = $this->token_repository->get( $chain_id, $address );
		if ( null === $token ) {
			// トークンデータが存在しない場合は新規登録を行うために少数点以下桁数とシンボルを取得する

			// チェーンに接続してERC20コントラクトから少数点以下桁数とシンボルを取得する
			$chain        = $this->chain_repository->get( $chain_id );
			$token_client = new TokenClient( $chain->rpcUrl(), $address );
			$decimals     = $token_client->decimals();
			$symbol       = $token_client->symbol();

			$token = new Token( $chain_id, $address, $symbol, $decimals, $is_payable );
		} else {
			$token->setIsPayable( $is_payable );
		}

		// トークン情報を保存
		$this->token_repository->save( $token );
	}
}
