<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Web3\TokenClient;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

/**
 * ERC20トークンの情報をブロックチェーンから取得して返します。
 */
class ResolveGetErc20Info {

	private ChainRepository $chain_repository;
	private OracleRepository $oracle_repository;
	private UserAccessChecker $user_access_checker;

	public function __construct(
		ChainRepository $chain_repository,
		OracleRepository $oracle_repository,
		UserAccessChecker $user_access_checker
	) {
		$this->chain_repository    = $chain_repository;
		$this->oracle_repository   = $oracle_repository;
		$this->user_access_checker = $user_access_checker;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );
		$address  = Address::from( $args['address'] );

		if ( $address === Ethers::zeroAddress() ) {
			// ERC20トークンの情報を取得するResolverのため、アドレスゼロも不許可
			throw new \InvalidArgumentException( '[6D00DB41] address is zero address.' );
		}

		$chain = $this->chain_repository->get( $chain_id );
		if ( is_null( $chain ) ) {
			throw new \InvalidArgumentException( '[DC8E36E6] chain data is not found. chain id: ' . $chain_id );
		} elseif ( ! $chain->connectable() ) {
			// チェーンが接続可能でない場合は例外を投げる
			throw new \InvalidArgumentException( '[84752B42] not connectable. chain id: ' . $chain_id );
		}

		$token_client = new TokenClient( $chain->rpcUrl(), $address );

		$symbol = $token_client->symbol();

		$symbol_callback = function () use ( $symbol ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
			return $symbol->value();
		};

		// レート変換可能かどうかを返すコールバック関数
		$rate_exchangeable_callback = function () use ( $symbol ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

			$oracles = $this->oracle_repository->all();
			// XXX/USD や XXX/ETH の接続可能なOracleが存在する場合はレート変換可能と判定
			$quote_symbols = array( 'USD', 'ETH' );
			foreach ( $quote_symbols as $quote_symbol ) {
				$filtered_oracles = ( new OraclesFilter() )
					->bySymbolPair( SymbolPair::from( $symbol, Symbol::from( $quote_symbol ) ) )
					->byConnectable()
					->apply( $oracles );
				if ( count( $filtered_oracles ) > 0 ) {
					return true;
				}
			}
			return false;
		};

		return array(
			'symbol'           => $symbol_callback,
			'rateExchangeable' => $rate_exchangeable_callback,
		);
	}
}
