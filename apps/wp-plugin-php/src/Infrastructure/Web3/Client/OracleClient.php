<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use phpseclib\Math\BigInteger;

class OracleClient {
	private readonly EthCallClient $client;
	private readonly Address $address;

	public function __construct( RpcUrl $rpc_url, Address $address ) {
		$this->client  = new EthCallClient( $rpc_url );
		$this->address = $address;
	}

	/**
	 * レートの小数点以下桁数を取得します。
	 */
	public function decimals(): Decimals {
		$res = $this->client->call( $this->address, ( new OracleAbi() )->get(), 'decimals' );

		return Decimals::from( (int) $res[0] );
	}

	/**
	 * オラクルの説明を取得します。
	 */
	public function description(): string {
		$res = $this->client->call( $this->address, ( new OracleAbi() )->get(), 'description' );

		assert( is_string( $res[0] ) );

		return $res[0];
	}

	/**
	 * 最新のレートを取得します。
	 * ※ `latestAnswer`は非推奨のため、`latestAnswer`で取得した値を使用
	 *
	 * @return BigInteger
	 */
	public function latestAnswer(): BigInteger {
		return $this->latestRoundData()->answer();
	}

	/**
	 * 最新のデータを取得します。
	 */
	public function latestRoundData(): OracleRoundData {
		$res = $this->client->call( $this->address, ( new OracleAbi() )->get(), 'latestRoundData' );

		return new OracleRoundData(
			new BigInteger( $res[0], 10 ),
			new BigInteger( $res[1], 10 ),
			new BigInteger( $res[2], 10 ),
			new BigInteger( $res[3], 10 ),
			new BigInteger( $res[4], 10 )
		);
	}
}


/**
 * @internal
 */
class OracleAbi {

	public function get(): array {
		$abi_json = <<<JSON
		{
			"abi": [
				{
					"inputs": [],
					"name": "decimals",
					"outputs": [
						{
							"internalType": "uint8",
							"name": "",
							"type": "uint8"
						}
					],
					"stateMutability": "view",
					"type": "function"
				},
				{
					"inputs": [],
					"name": "description",
					"outputs": [
						{
							"internalType": "string",
							"name": "",
							"type": "string"
						}
					],
					"stateMutability": "view",
					"type": "function"
				},
				{
					"inputs": [],
					"name": "latestRoundData",
					"outputs": [
						{
							"internalType": "uint80",
							"name": "roundId",
							"type": "uint80"
						},
						{
							"internalType": "int256",
							"name": "answer",
							"type": "int256"
						},
						{
							"internalType": "uint256",
							"name": "startedAt",
							"type": "uint256"
						},
						{
							"internalType": "uint256",
							"name": "updatedAt",
							"type": "uint256"
						},
						{
							"internalType": "uint80",
							"name": "answeredInRound",
							"type": "uint80"
						}
					],
					"stateMutability": "view",
					"type": "function"
				}
			]
		}
		JSON;

		return json_decode( $abi_json, true )['abi'];
	}
}

/** @internal */
class OracleRoundData {
	public function __construct( BigInteger $round_id, BigInteger $answer, BigInteger $started_at, BigInteger $updated_at, BigInteger $answered_in_round ) {
		$this->round_id          = $round_id;
		$this->answer            = $answer;
		$this->started_at        = $started_at;
		$this->updated_at        = $updated_at;
		$this->answered_in_round = $answered_in_round;
	}

	private readonly BigInteger $round_id;
	private readonly BigInteger $answer;
	private readonly BigInteger $started_at;
	private readonly BigInteger $updated_at;
	private readonly BigInteger $answered_in_round;

	public function roundId(): BigInteger {
		return $this->round_id;
	}

	public function answer(): BigInteger {
		return $this->answer;
	}

	public function startedAt(): BigInteger {
		return $this->started_at;
	}

	public function updatedAt(): BigInteger {
		return $this->updated_at;
	}

	public function answeredInRound(): BigInteger {
		return $this->answered_in_round;
	}
}
