<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\UpdateConfirmations;
use Cornix\Serendipity\Core\Constant\Config;

class SetConfirmationsResolver extends ResolverBase {

	public function __construct(
		UpdateConfirmations $update_confirmations,
		UserAccessChecker $user_access_checker,
		TransactionService $transaction_service
	) {
		$this->update_confirmations = $update_confirmations;
		$this->user_access_checker  = $user_access_checker;
		$this->transaction_service  = $transaction_service;
	}

	private UpdateConfirmations $update_confirmations;
	private UserAccessChecker $user_access_checker;
	private TransactionService $transaction_service;

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var int */
		$chain_id_value = $args['chainID'];
		/** @var string|null */
		$confirmations_value = $args['confirmations'] ?? null;
		$confirmations       = $confirmations_value ?? Config::MIN_CONFIRMATIONS; // nullが指定された場合は既定値を設定 TODO: nullableを廃止

		// confirmationsを保存
		try {
			$this->transaction_service->beginTransaction();

			$this->update_confirmations->handle( $chain_id_value, $confirmations );

			$this->transaction_service->commit();
		} catch ( \Throwable $e ) {
			$this->transaction_service->rollback();
			throw $e;
		}

		return true;
	}
}
