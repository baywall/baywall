<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class LogTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->id = (int) $record->id;

		$this->import( $record );
	}

	protected int $id;
	protected string $created_at;
	protected string $level;
	protected string $category;
	protected string $message;

	public function id(): int {
		return $this->id;
	}

	public function createdAt(): string {
		return $this->created_at;
	}

	public function level(): string {
		return $this->level;
	}

	public function category(): string {
		return $this->category;
	}

	public function message(): string {
		return $this->message;
	}
}
