<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\Port\TransactionService;

class TransactionRunner {
	public function __construct( private readonly TransactionService $transaction_service ) {}

	/**
	 * トランザクション内で処理を実行します。
	 *
	 * @template T
	 * @param callable():T $func
	 * @return T
	 */
	public function transactional( callable $func ) {
		$this->transaction_service->beginTransaction();
		try {
			$result = $func();
			$this->transaction_service->commit();
			return $result;
		} catch ( \Throwable $e ) {
			$this->transaction_service->rollback();
			throw $e;
		}
	}
}
