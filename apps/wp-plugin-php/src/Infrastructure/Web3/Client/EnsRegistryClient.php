<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Factory\ContractFactory;
use Web3\Contract;

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

	private Contract $registry;

	public function __construct( RpcUrl $rpc_url ) {
		$this->registry = ( new ContractFactory() )->create( $rpc_url, ( new EnsRegistryAbi() )->get(), Address::from( self::REGISTRY_ADDRESS ) );
	}

	/**
	 * 指定されたノード（namehash）に対応する Resolver コントラクトのアドレスを取得します。
	 *
	 * @param string $node namehash（`0x` で始まる 64 文字の HEX 文字列）
	 * @return string Resolver コントラクトのアドレス
	 */
	public function resolver( string $node ): string {
		/** @var string|null */
		$result = null;
		$this->registry->call(
			'resolver',
			$node,
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					throw $err;
				}
				assert( is_string( $res[0] ?? null ), '[EEE7B02F] resolver address is not string.' );
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
