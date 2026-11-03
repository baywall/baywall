<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Port\AdminNotificationService;
use Baywall\Core\Application\Port\SellerWalletChangeNotificationMessageProvider;
use Baywall\Core\Application\Port\TransactionService;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Seller;
use Baywall\Core\Domain\Repository\SellerRepository;
use Baywall\Core\Domain\Service\SignatureService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Signature;
use Baywall\Core\Domain\ValueObject\SigningMessage;

class SaveSeller {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly SellerRepository $seller_repository,
		private readonly SignatureService $signature_service,
		private readonly TransactionService $transaction_service,
		private readonly AdminNotificationService $admin_notification_service,
		private readonly SellerWalletChangeNotificationMessageProvider $seller_wallet_change_notification_message_provider
	) {}

	/** 販売者情報を保存します */
	public function handle( Address $address, SigningMessage $signing_message, Signature $signature ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		// 署名からアドレスを復元し、指定されたアドレスと一致することを確認
		$recovered_address = $this->signature_service->verifyMessage( $signing_message, $signature );
		if ( ! $recovered_address->equals( $address ) ) {
			throw new \InvalidArgumentException( "[EA91F2D4] Address mismatch. Expected: {$address}, got: {$recovered_address}" );
		}

		// 変更通知の判定用に、変更前の販売者情報を取得
		$previous = $this->seller_repository->get();

		// 販売者情報を保存
		$result = $this->transaction_service->transactional(
			function () use ( $address, $signing_message, $signature ) {
				$this->seller_repository->save(
					new Seller(
						$address,
						$signing_message,
						$signature,
					)
				);
				return true;
			}
		);

		// 保存確定後に通知する（トランザクションがロールバックされた場合は送信しない）。
		// notify() は best-effort 契約のため、例外ハンドリングは行わない
		if ( $previous !== null && ! $previous->address()->equals( $address ) ) {
			$this->admin_notification_service->notify(
				$this->seller_wallet_change_notification_message_provider->title(),
				$this->seller_wallet_change_notification_message_provider->message( $previous->address(), $address )
			);
		}

		return $result;
	}
}
