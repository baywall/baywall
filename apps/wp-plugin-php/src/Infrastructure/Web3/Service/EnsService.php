<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Util\Strings;
use Cornix\Serendipity\Core\Infrastructure\Web3\Exception\ResolverNotFoundException;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\EnsRegistryClientFactory;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\EnsResolverClientFactory;

/**
 * ENS から情報を取得するサービスです。
 */
class EnsService {

	public function __construct( EnsRegistryClientFactory $ens_registry_client_factory, EnsResolverClientFactory $ens_resolver_client_factory ) {
		$this->ens_registry_client_factory = $ens_registry_client_factory;
		$this->ens_resolver_client_factory = $ens_resolver_client_factory;
	}
	private EnsRegistryClientFactory $ens_registry_client_factory;
	private EnsResolverClientFactory $ens_resolver_client_factory;

	/**
	 * 指定した ENS 名のテキストレコードを取得します。
	 *
	 * ENS レジストリから Resolver アドレスを解決し、Resolver からテキストレコードを取得します。
	 * Resolver が未設定（ゼロアドレス）の場合は空文字列を返します。
	 *
	 * @param RpcUrl $rpc_url  RPC エンドポイント
	 * @param string $ens_name ENS 名（例: 'baywall.eth'）。`.eth` で終わる必要があります。
	 * @param string $key      テキストレコードのキー（例: 'url', 'avatar', 'com.twitter' 等）
	 * @return string テキストレコードの値
	 * @throws \InvalidArgumentException       ENS 名が `.eth` で終わらない場合にスローされます。
	 * @throws ResolverNotFoundException       Resolver が未設定（ゼロアドレス）の場合にスローされます。
	 */
	public function getText( RpcUrl $rpc_url, string $ens_name, string $key ): string {
		// ENS 名は `.eth` で終わることを必須とする
		if ( ! Strings::ends_with( $ens_name, '.eth' ) ) {
			throw new \InvalidArgumentException( "[28115649] ens_name must end with '.eth'. - {$ens_name}" );
		}

		$node = Ethers::namehash( $ens_name );

		// 1. Registry から Resolver アドレスを取得
		$registry_client  = $this->ens_registry_client_factory->create( $rpc_url );
		$resolver_address = $registry_client->resolver( $node );

		// 2. Resolver 未設定（ゼロアドレス）の場合は例外をスロー
		if ( Address::zero()->equals( Address::from( $resolver_address ) ) ) {
			throw new ResolverNotFoundException( "[C9E307B7] Resolver is not set for ens_name. - {$ens_name}" );
		}

		// 3. Resolver からテキストレコードを取得
		$resolver_client = $this->ens_resolver_client_factory->create( $rpc_url, Address::from( $resolver_address ) );
		return $resolver_client->text( $node, $key );
	}
}
