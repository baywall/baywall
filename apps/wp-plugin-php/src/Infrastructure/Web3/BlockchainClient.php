<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject\EthBlock;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use phpseclib\Math\BigInteger;
use ReflectionClass;
use Web3\Eth;
use Web3\Formatters\BigNumberFormatter;
use Web3\Methods\EthMethod;

// memo.
// Ethを継承してリトライを行うクラスを作成する方法は、名前空間やクラス名がEthクラス内部で使用されておりややこしくなるため不採用
// ここでは各メソッドでリトライオブジェクトを使用するように実装している

class BlockchainClient {
	public function __construct( RpcUrl $rpc_url ) {
		$this->rpc_url = $rpc_url;
		$this->timeout = Config::BLOCKCHAIN_REQUEST_TIMEOUT;
		$this->retryer = new BlockchainRetryer();
	}
	private RpcUrl $rpc_url;
	private float $timeout;
	private BlockchainRetryer $retryer;

	private function eth(): Eth {
		return new Eth( $this->rpc_url->value(), $this->timeout );
	}

	/**
	 * `eth_chainId`を呼び出します
	 */
	public function ethChainId(): ChainId {
		$eth = $this->eth();

		// Ethオブジェクトの内容を操作することで`eth_chainId`メソッドの追加を行う
		{
			$reflectionClass = new ReflectionClass( get_class( $eth ) );
			$property        = $reflectionClass->getProperty( 'allowedMethods' );
			$property->setAccessible( true );
			/** @var string[] */
			$allowedMethods = $property->getValue( $eth );
			assert( ! in_array( 'eth_chainId', $allowedMethods, true ), '[36C3ECD5] `eth_chainId` method is already allowed.' );
			$allowedMethods[] = 'eth_chainId';
			$property->setValue( $eth, $allowedMethods ); // 許可するメソッド一覧に`eth_chainId`を追加

			$methods_property = $reflectionClass->getProperty( 'methods' );
			$methods_property->setAccessible( true );
			$methods                = $methods_property->getValue( $eth );
			$methods['eth_chainId'] = new ChainIdMethod( 'eth_chainId', array() );  // `eth_chainId`メソッド呼び出し時に使うクラスを設定
			$methods_property->setValue( $eth, $methods );
		}

		/** @var ChainId|null */
		$chain_id = null;
		$this->retryer->execute(
			function () use ( $eth, &$chain_id ) {
				$eth->chainId(
					function ( $err, BigInteger $res ) use ( &$chain_id ) {
						if ( $err ) {
							throw $err;
						}
						$chain_id = ChainId::from( (int) $res->toString() );
					}
				);
			}
		);
		assert( ! is_null( $chain_id ), '[1BAA2783] Failed to get chain ID.' );

		return $chain_id;
	}

	/**
	 * `eth_getBlockByNumber`を呼び出します。
	 *
	 * @param string|BlockNumber|BlockTag $block_number_or_tag
	 */
	public function ethGetBlockByNumber( $block_number_or_tag ): EthBlock {
		if ( $block_number_or_tag instanceof BlockNumber ) {
			$block_number = $block_number_or_tag->hex();
		} elseif ( $block_number_or_tag instanceof BlockTag ) {
			$block_number = $block_number_or_tag->value();
		} else {
			throw new \InvalidArgumentException( '[FDB7CEF6] Invalid argument type. Expected BlockNumber or BlockTag. - ' . var_export( $block_number_or_tag, true ) );
		}

		/** @var null|EthBlock */
		$result = null;
		$this->retryer->execute(
			function () use ( $block_number, &$result ) {
				$this->eth()->getBlockByNumber(
					$block_number,
					false, // false: トランザクションの詳細を取得しない
					function ( $err, $res ) use ( &$result ) {
						if ( $err ) {
							throw $err;
						}
						$result = EthBlock::from( $res );
					}
				);
			}
		);
		assert( null !== $result, '[F6805A68] Result should not be null after retry.' );

		return $result;
	}

	/**
	 * `eth_blockNumber`を呼び出します。
	 *
	 * 最新(latest)のブロック番号を取得します。
	 */
	public function ethBlockNumber(): BlockNumber {
		/** @var BlockNumber|null */
		$block_number = null;
		$this->retryer->execute(
			function () use ( &$block_number ) {
				$this->eth()->blockNumber(
					function ( $err, BigInteger $res ) use ( &$block_number ) {
						if ( $err ) {
							throw $err;
						}
						$block_number = BlockNumber::from( $res );
					}
				);
			}
		);

		return $block_number;
	}

	/**
	 * `eth_getBalance`を呼び出します。
	 *
	 * アカウントの残高を取得します。
	 */
	public function ethGetBalance( Address $address ): Amount {

		/** @var Amount|null */
		$balance = null;
		$this->retryer->execute(
			function () use ( $address, &$balance ) {
				$this->eth()->getBalance(
					$address->value(),
					function ( $err, BigInteger $res ) use ( &$balance ) {
						if ( $err ) {
							throw $err;
						}
						$balance = Amount::from( $res->toString() );
					}
				);
			}
		);

		return $balance;
	}

	public function ethGetLogs( ...$args ) {
		$this->retryer->execute(
			function () use ( $args ) {
				$this->eth()->getLogs( ...$args );
			}
		);
	}
}


/** @internal */
class ChainIdMethod extends EthMethod {
	protected $validators       = array();
	protected $inputFormatters  = array();
	protected $outputFormatters = array( BigNumberFormatter::class );
	protected $defaultValues    = array();
}
