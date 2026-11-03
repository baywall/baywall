<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Abi\AbiCodec;
use Baywall\Core\Infrastructure\Web3\Exception\ResolverNotFoundException;
use Baywall\Core\Infrastructure\Web3\Exception\RpcErrorException;
use Baywall\Core\Infrastructure\Web3\Exception\RpcException;
use Baywall\Core\Lib\Ether\Ethers;

/**
 * ENSv2 の Universal Resolver コントラクトのクライアントです。
 * ENSv1 レジストリで Resolver が未設定（ゼロアドレス）の名前の解決に使用します。
 *
 * @see packages/lib-ethers-ens/src/ens/createEnsV2UniversalResolverContract.ts
 */
class EnsV2UniversalResolverClient {

	/**
	 * ENSv2 の Universal Resolver（UpgradableUniversalResolverProxy）のアドレス（チェーン共通）
	 *
	 * @see https://docs.ens.domains/learn/deployments
	 */
	private const UNIVERSAL_RESOLVER_ADDRESS = '0xeEeEEEeE14D718C2B47D9923Deab1335E144EeEe';

	private readonly EthCallClient $client;

	public function __construct( RpcUrl $rpc_url, ?RpcTransport $transport = null ) {
		$this->client = new EthCallClient( $rpc_url, $transport );
	}

	/**
	 * Universal Resolver 経由でテキストレコードを取得します。
	 *
	 * `resolve(dnsEncode(name), text(namehash(name), key))` を eth_call し、
	 * 応答 result（ABI エンコード済みの文字列）を UTF-8 文字列として返します。
	 *
	 * @param string $ens_name ENS 名（例: 'baywall.eth'）。`.eth` で終わる必要があります。
	 * @param string $key      テキストレコードのキー（例: 'url', 'avatar', 'com.twitter' 等）
	 * @return string テキストレコードの値（未設定の場合は空文字列）
	 * @throws \InvalidArgumentException ENS 名の正規化（`namehash` / `dnsEncode`）で拒否される場合（そのまま伝播）
	 * @throws ResolverNotFoundException 名前が解決できない場合（revert・空結果・Resolver ゼロアドレス）
	 * @throws RpcException              RPC トランスポート障害の場合（そのまま透過）
	 */
	public function text( string $ens_name, string $key ): string {
		$codec = new AbiCodec();
		$abi   = ( new EnsV2UniversalResolverAbi() )->get();

		// profile calldata は ENSv1 Resolver と同一の text(bytes32,string)。Universal Resolver が name から namehash を組み立てて解決する
		// 入力検証例外（namehash / dnsEncode）は ResolverNotFoundException にすり替えずそのまま伝播させる
		$profile_calldata = $codec->encodeCall( $abi, 'text', array( Ethers::namehash( $ens_name ), $key ) );
		$dns_name         = Ethers::dnsEncode( $ens_name );

		try {
			$res = $this->client->call(
				Address::from( self::UNIVERSAL_RESOLVER_ADDRESS ),
				$abi,
				'resolve',
				array( $dns_name, $profile_calldata )
			);
			assert( is_string( $res[0] ?? null ), '[E29B9201] resolve result is not string.' );
			assert( is_string( $res[1] ?? null ), '[1ABBED3B] resolver address is not string.' );
			if ( Address::zero()->equals( Address::from( $res[1] ) ) ) {
				throw new ResolverNotFoundException( "[C28781C1] ENSv2 resolution failed. - {$ens_name}" );
			}
			$decoded = $codec->decodeResult( $abi, 'text', $res[0] );
		} catch ( RpcErrorException $e ) {
			// revert（未登録名等の解決失敗）は型付きの未解決に正規化する
			throw new ResolverNotFoundException( "[C28781C1] ENSv2 resolution failed. - {$ens_name}", 0, $e );
		} catch ( \InvalidArgumentException $e ) {
			// 空結果・デコード不能も未解決として扱う
			throw new ResolverNotFoundException( "[C28781C1] ENSv2 resolution failed. - {$ens_name}", 0, $e );
		}

		assert( is_string( $decoded[0] ?? null ), '[DC0BEE15] text record is not string.' );
		return $decoded[0];
	}
}

/**
 * @internal
 */
class EnsV2UniversalResolverAbi {
	/** @return list<array<string, mixed>> */
	public function get(): array {
		$abi_json = <<<JSON
		{
			"abi": [
				{
					"inputs": [
						{
							"internalType": "bytes",
							"name": "name",
							"type": "bytes"
						},
						{
							"internalType": "bytes",
							"name": "data",
							"type": "bytes"
						}
					],
					"name": "resolve",
					"outputs": [
						{
							"internalType": "bytes",
							"name": "result",
							"type": "bytes"
						},
						{
							"internalType": "address",
							"name": "resolver",
							"type": "address"
						}
					],
					"stateMutability": "view",
					"type": "function"
				},
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
