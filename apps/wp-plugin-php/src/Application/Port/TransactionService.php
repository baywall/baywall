<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

interface TransactionService {
	public function beginTransaction(): void;

	public function commit(): void;

	public function rollback(): void;
}
