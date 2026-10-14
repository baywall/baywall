<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;

/**
 * ENS Resolver コントラクトのクライアントです。
 * テキストレコードの取得等に使用します。
 *
 * @see packages/lib-ethers-ens/src/ens/createEnsResolverContract.ts
 */
class EnsResolverClient {

	private readonly EthCallClient $client;
	private readonly Address $address;

	/**
	 * @param RpcUrl  $rpc_url RPC エンドポイント
	 * @param Address $address Resolver コントラクトのアドレス
	 */
	public function __construct( RpcUrl $rpc_url, Address $address ) {
		$this->client  = new EthCallClient( $rpc_url );
		$this->address = $address;
	}

	/**
	 * 指定されたノード（namehash）のテキストレコードを取得します。
	 *
	 * @param string $node namehash（`0x` で始まる 64 文字の HEX 文字列）
	 * @param string $key  テキストレコードのキー（例: 'url', 'avatar', 'com.twitter' 等）
	 * @return string テキストレコードの値（未設定の場合は空文字列）
	 */
	public function text( string $node, string $key ): string {
		$res = $this->client->call( $this->address, ( new EnsResolverAbi() )->get(), 'text', array( $node, $key ) );

		assert( is_string( $res[0] ?? null ), '[97A47446] text record is not string.' );

		return $res[0];
	}
}

/**
 * @internal
 */
class EnsResolverAbi {

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
						},
						{
							"internalType": "string",
							"name": "key",
							"type": "string"
						}
					],
					"name": "text",
					"outputs": [
						{
							"internalType": "string",
							"name": "",
							"type": "string"
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
