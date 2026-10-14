<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Abi\AbiCodec;
use Baywall\Core\Infrastructure\Web3\BlockchainRetryer;

/**
 * read-only のコントラクト呼び出し（eth_call）を実行するクラス。
 *
 * 旧 web3.php `Contract::call` の代替。`AbiCodec` で calldata を組み立て、
 * `RpcClient` 経由で `eth_call` を送り、応答をデコードして位置配列で返す。
 * リトライは `BlockchainRetryer`（旧 `RetryContract::call` と同一設定）で行う。
 */
class EthCallClient {
	public function __construct( RpcUrl $rpc_url ) {
		$this->retryer    = new BlockchainRetryer();
		$this->rpc_client = new RpcClient( $rpc_url );
	}
	private readonly BlockchainRetryer $retryer;
	private readonly RpcClient $rpc_client;

	/**
	 * read-only のコントラクト関数を呼び出します。
	 *
	 * @param Address          $to     呼び出し先コントラクトのアドレス
	 * @param array<int,mixed> $abi    ABI定義配列
	 * @param string           $method 関数名
	 * @param array<int,mixed> $args   引数
	 * @return list<mixed> デコードされた戻り値（位置配列）
	 */
	public function call( Address $to, array $abi, string $method, array $args = array() ): array {
		$codec = new AbiCodec();

		/** @var list<mixed>|null */
		$result = null;
		$this->retryer->execute(
			function () use ( $to, $abi, $method, $args, $codec, &$result ) {
				$data   = $codec->encodeCall( $abi, $method, $args );
				$hex    = $this->rpc_client->request(
					'eth_call',
					array(
						array(
							'to'   => $to->value(),
							'data' => $data,
						),
						'latest',
					)
				);
				$result = $codec->decodeResult( $abi, $method, $hex );
			}
		);
		assert( is_array( $result ) );

		return $result;
	}
}
