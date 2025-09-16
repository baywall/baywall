<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

abstract class TransactionService {
	abstract public function beginTransaction(): void;
	abstract public function commit(): void;
	abstract public function rollback(): void;
}
