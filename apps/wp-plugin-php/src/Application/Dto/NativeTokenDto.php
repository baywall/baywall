<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Dto;

use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Domain\ValueObject\Symbol;

/** ネイティブトークン情報 */
readonly class NativeTokenDto {
	public function __construct(
		public readonly Symbol $symbol,
		public readonly string $name,
		public readonly Decimals $decimals
	) {}
}
