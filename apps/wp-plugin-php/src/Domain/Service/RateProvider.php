<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\Exception\RateNotFoundException;
use Baywall\Core\Domain\ValueObject\Rate;
use Baywall\Core\Domain\ValueObject\SymbolPair;

/**
 * 指定した通貨ペアのレートを取得するサービスインターフェース
 */
interface RateProvider {
	/**
	 * 指定した通貨ペアのレートを取得します。
	 *
	 * @throws RateNotFoundException レートが見つからない場合にスローされます。
	 */
	public function getRate( SymbolPair $symbol_pair ): Rate;

	/**
	 * 指定した通貨ペアのレート取得がサポートされているかどうかを取得します。
	 */
	public function supports( SymbolPair $symbol_pair ): bool;
}
