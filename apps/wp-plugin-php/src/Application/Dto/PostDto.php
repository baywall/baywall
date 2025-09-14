<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Dto;

class PostDto {

	public function __construct( int $id, string $title ) {
		$this->id    = $id;
		$this->title = $title;
	}

	public int $id;
	public string $title;
}
