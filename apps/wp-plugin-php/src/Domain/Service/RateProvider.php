<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;

/**
 * 指定した通貨ペアのレートを取得するサービスインターフェース
 */
interface RateProvider {
	/**
	 * 指定した通貨ペアのレートを取得します。
	 */
	public function getRate( SymbolPair $symbol_pair ): Rate;
}
