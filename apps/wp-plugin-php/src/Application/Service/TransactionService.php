<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

abstract class TransactionService {
	abstract public function beginTransaction(): void;
	abstract public function commit(): void;
	abstract public function rollback(): void;

	/**
	 * トランザクション内で処理を実行します。
	 *
	 * @template T
	 * @param callable():T $func
	 * @return T
	 */
	public function transactional( callable $func ) {
		$this->beginTransaction();
		try {
			$result = $func();
			$this->commit();
			return $result;
		} catch ( \Throwable $e ) {
			$this->rollback();
			throw $e;
		}
	}
}
