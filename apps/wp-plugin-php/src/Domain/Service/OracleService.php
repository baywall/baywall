<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Rate;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Domain\ValueObject\SymbolPair;

/** オラクルコントラクトからレート情報を取得するインターフェース */
interface OracleService {
	/** 最新のレートを取得します。通貨ペアは `description` から導出します */
	public function getRate( RpcUrl $rpc_url, Address $address ): Rate;

	/** 通貨ペアを取得します。`description` から導出します */
	public function symbolPair( RpcUrl $rpc_url, Address $address ): SymbolPair;
}
