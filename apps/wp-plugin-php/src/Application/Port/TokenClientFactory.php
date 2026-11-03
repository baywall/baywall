<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;

interface TokenClientFactory {
	/** トークンクライアントのインスタンスを生成します。 */
	public function create( RpcUrl $rpc_url, Address $contract_address ): TokenClient;
}
