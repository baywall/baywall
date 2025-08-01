<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Service\RateProvider;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\OracleClientFactory;

class OracleRateProviderImpl implements RateProvider {
	public function __construct( OracleRepository $oracle_repository, OracleClientFactory $oracle_client_factory ) {
		$this->oracle_repository     = $oracle_repository;
		$this->oracle_client_factory = $oracle_client_factory;
	}
	private OracleRepository $oracle_repository;
	private OracleClientFactory $oracle_client_factory;

	public function getRate( SymbolPair $symbol_pair ): ?Rate {
		// 接続可能なオラクルを取得
		$oracle = $this->getConnectableOracle( $symbol_pair );
		if ( null === $oracle ) {
			return null; // 利用可能なオラクルがない場合はnullを返す
		}

		// オラクルへ接続するインスタンスを作成
		$oracle_client = $this->oracle_client_factory->create( $oracle );

		// オラクルから小数点以下桁数とレートを取得
		$decimals = $oracle_client->decimals();
		$answer   = $oracle_client->latestAnswer();

		$rate_amount = Amount::fromBaseUnitAndDecimals( $answer->toString(), $decimals );
		return Rate::from( $symbol_pair, $rate_amount );
	}

	/**
	 * 指定された通貨ペアのレートが取得可能なオラクルを取得します。
	 *
	 * @param SymbolPair $symbol_pair
	 * @return Oracle|null
	 */
	private function getConnectableOracle( SymbolPair $symbol_pair ): ?Oracle {
		$connectable_oracles = ( new OraclesFilter() )
			->bySymbolPair( $symbol_pair )
			->byConnectable()
			->apply( $this->oracle_repository->all() );

		if ( empty( $connectable_oracles ) ) {
			return null; // 利用可能なオラクルがない場合はnullを返す
		}

		// 接続可能なオラクルが複数ある場合は、最初のものを使用(最初である必要は無いので、適宜変更可能)
		$oracle = array_values( $connectable_oracles )[0];

		return $oracle;
	}
}
