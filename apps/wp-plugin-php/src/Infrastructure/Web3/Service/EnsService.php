<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Lib\Ether\Ethers;
use Baywall\Core\Lib\Util\Strings;
use Baywall\Core\Infrastructure\Web3\Exception\ResolverNotFoundException;
use Baywall\Core\Infrastructure\Web3\Factory\EnsRegistryClientFactory;
use Baywall\Core\Infrastructure\Web3\Factory\EnsResolverClientFactory;
use Baywall\Core\Infrastructure\Web3\Factory\EnsV2UniversalResolverClientFactory;

/**
 * ENS から情報を取得するサービスです。
 */
class EnsService {

	public function __construct(
		private readonly EnsRegistryClientFactory $ens_registry_client_factory,
		private readonly EnsResolverClientFactory $ens_resolver_client_factory,
		private readonly EnsV2UniversalResolverClientFactory $ens_v2_universal_resolver_client_factory
	) {}

	/**
	 * 指定した ENS 名のテキストレコードを取得します。
	 *
	 * ENS レジストリから Resolver アドレスを解決し、Resolver からテキストレコードを取得します。
	 * Resolver が未設定（ゼロアドレス）の場合は ENSv2 の Universal Resolver 経由で解決を試み、
	 * それでも解決できない場合は ResolverNotFoundException をスローします。
	 *
	 * @param RpcUrl $rpc_url  RPC エンドポイント
	 * @param string $ens_name ENS 名（例: 'baywall.eth'）。`.eth` で終わる必要があります。
	 * @param string $key      テキストレコードのキー（例: 'url', 'avatar', 'com.twitter' 等）
	 * @return string テキストレコードの値
	 * @throws \InvalidArgumentException       ENS 名が `.eth` で終わらない場合、または正規化で拒否される場合（空ラベル・未対応文字・`xn--` ラベル）にスローされます。
	 * @throws ResolverNotFoundException       ENSv1 / ENSv2 のどちらでも解決できない場合にスローされます。
	 */
	public function getText( RpcUrl $rpc_url, string $ens_name, string $key ): string {
		// ENS 名は `.eth` で終わることを必須とする（正規化前の名前で判定し、空名等はここで拒否する）
		if ( ! Strings::ends_with( strtolower( $ens_name ), '.eth' ) ) {
			throw new \InvalidArgumentException( "[28115649] ens_name must end with '.eth'. - {$ens_name}" );
		}

		$ens_name = Ethers::ensNormalize( $ens_name );
		$node     = Ethers::namehash( $ens_name );

		// 1. Registry から Resolver アドレスを取得（ENSv1 probe）
		$registry_client  = $this->ens_registry_client_factory->create( $rpc_url );
		$resolver_address = $registry_client->resolver( $node );

		// 2. Resolver 未設定（ゼロアドレス）の場合は ENSv2 の Universal Resolver 経由で解決を試みる
		if ( Address::zero()->equals( Address::from( $resolver_address ) ) ) {
			try {
				return $this->ens_v2_universal_resolver_client_factory->create( $rpc_url )->text( $ens_name, $key );
			} catch ( ResolverNotFoundException $e ) {
				throw new ResolverNotFoundException( "[C9E307B7] Resolver is not set for ens_name. - {$ens_name}", 0, $e );
			}
		}

		// 3. Resolver からテキストレコードを取得
		$resolver_client = $this->ens_resolver_client_factory->create( $rpc_url, Address::from( $resolver_address ) );
		return $resolver_client->text( $node, $key );
	}
}
