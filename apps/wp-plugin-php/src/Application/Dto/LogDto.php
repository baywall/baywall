<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Dto;

/**
 * ログ1件分のDTO
 */
readonly class LogDto {
	public function __construct(
		public readonly int $id,
		public readonly int $createdAt,
		public readonly string $level,
		public readonly string $message
	) {}
}
