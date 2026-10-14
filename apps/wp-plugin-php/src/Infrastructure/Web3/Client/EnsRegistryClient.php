<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;

/**
 * ENS レジストリコントラクトのクライアントです。
 * ノード（namehash）から Resolver アドレスを取得する際に使用します。
 *
 * @see packages/lib-ethers-ens/src/ens/createEnsRegistryContract.ts
 */
class EnsRegistryClient {

	/**
	 * ENS レジストリのコントラクトアドレス（メインネット、Sepolia 共通）
	 *
	 * @see https://etherscan.io/address/0x00000000000C2E074eC69A0dFb2997BA6C7d2e1e
	 * @see https://sepolia.etherscan.io/address/0x00000000000C2E074eC69A0dFb2997BA6C7d2e1e
	 */
	private const REGISTRY_ADDRESS = '0x00000000000C2E074eC69A0dFb2997BA6C7d2e1e';

	private readonly EthCallClient $client;

	public function __construct( RpcUrl $rpc_url ) {
		$this->client = new EthCallClient( $rpc_url );
	}

	/**
	 * 指定されたノード（namehash）に対応する Resolver コントラクトのアドレスを取得します。
	 *
	 * @param string $node namehash（`0x` で始まる 64 文字の HEX 文字列）
	 * @return string Resolver コントラクトのアドレス
	 */
	public function resolver( string $node ): string {
		$res = $this->client->call( Address::from( self::REGISTRY_ADDRESS ), ( new EnsRegistryAbi() )->get(), 'resolver', array( $node ) );

		assert( is_string( $res[0] ?? null ), '[EEE7B02F] resolver address is not string.' );

		return $res[0];
	}
}

/**
 * @internal
 */
class EnsRegistryAbi {

	public function get(): array {
		$abi_json = <<<JSON
		{
			"abi": [
				{
					"inputs": [
						{
							"internalType": "bytes32",
							"name": "node",
							"type": "bytes32"
						}
					],
					"name": "resolver",
					"outputs": [
						{
							"internalType": "address",
							"name": "",
							"type": "address"
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
