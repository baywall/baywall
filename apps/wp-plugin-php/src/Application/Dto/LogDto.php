<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Dto;

/**
 * ログ1件分のDTO
 */
class LogDto {

	public int $id;
	public int $createdAt;
	public string $level;
	public string $category;
	public string $message;

	public function __construct(
		int $id,
		int $createdAt,
		string $level,
		string $category,
		string $message
	) {
		$this->id        = $id;
		$this->createdAt = $createdAt;
		$this->level     = $level;
		$this->category  = $category;
		$this->message   = $message;
	}
}
