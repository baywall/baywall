<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Factory\ContractFactory;
use Web3\Contract;

/**
 * ENS Resolver コントラクトのクライアントです。
 * テキストレコードの取得等に使用します。
 *
 * @see packages/lib-ethers-ens/src/ens/createEnsResolverContract.ts
 */
class EnsResolverClient {

	private Contract $resolver;

	/**
	 * @param RpcUrl  $rpc_url RPC エンドポイント
	 * @param Address $address Resolver コントラクトのアドレス
	 */
	public function __construct( RpcUrl $rpc_url, Address $address ) {
		$this->resolver = ( new ContractFactory() )->create( $rpc_url, ( new EnsResolverAbi() )->get(), $address );
	}

	/**
	 * 指定されたノード（namehash）のテキストレコードを取得します。
	 *
	 * @param string $node namehash（`0x` で始まる 64 文字の HEX 文字列）
	 * @param string $key  テキストレコードのキー（例: 'url', 'avatar', 'com.twitter' 等）
	 * @return string テキストレコードの値（未設定の場合は空文字列）
	 */
	public function text( string $node, string $key ): string {
		/** @var string|null */
		$result = null;
		$this->resolver->call(
			'text',
			$node,
			$key,
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					throw $err;
				}
				assert( is_string( $res[0] ?? null ), '[97A47446] text record is not string.' );
				$result = $res[0];
			}
		);

		assert( is_string( $result ) );
		return $result;
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
