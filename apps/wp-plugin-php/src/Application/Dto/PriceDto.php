<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Dto;

/** 価格 */
class PriceDto {

	public function __construct(
		public readonly string $amount,
		public readonly string $symbol
	) {}
}
