<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Dto;

/** 有料部分のコンテンツ量 */
readonly class SellingContentDto {
	public function __construct(
		public readonly int $character_count,
		public readonly int $image_count
	) {}
}
