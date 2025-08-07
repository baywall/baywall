<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

interface TransactionService {
	public function beginTransaction(): void;
	public function commit(): void;
	public function rollback(): void;
}
