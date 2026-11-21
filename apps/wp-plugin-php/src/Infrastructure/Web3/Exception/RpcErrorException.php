<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Exception;

/**
 * JSON-RPC の `error` 応答（revert 等）で発生する例外。
 *
 * トランスポート障害等の `RpcException` と区別し、
 * 呼び出し側が「コントラクトの revert」と「RPCの通信障害」を分けて扱えるようにする。
 */
class RpcErrorException extends RpcException {
}
