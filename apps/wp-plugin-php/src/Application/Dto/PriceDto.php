<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Dto;

/** 価格 */
class PriceDto {
	public string $amount;
	public string $symbol;

	public function __construct( string $amount, string $symbol ) {
		$this->amount = $amount;
		$this->symbol = $symbol;
	}
}
