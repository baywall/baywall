<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject\Interfaces;

/**
 * value objectを表すインタフェース
 */
interface ValueObject {
	public function __toString(): string;
}
