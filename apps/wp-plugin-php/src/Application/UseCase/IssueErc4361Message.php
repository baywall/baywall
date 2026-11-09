<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Repository\Erc4361NonceRepository;
use Baywall\Core\Application\Port\Erc4361NonceGenerator;
use Baywall\Core\Application\Service\Erc4361Service;
use Baywall\Core\Application\Service\TransactionRunner;
use Baywall\Core\Application\ValueObject\Erc4361Message;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class IssueErc4361Message {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly TransactionRunner $transaction_runner,
		private readonly Erc4361NonceRepository $erc4361_nonce_repository,
		private readonly Erc4361NonceGenerator $erc4361_nonce_generator,
		private readonly Erc4361Service $erc4361_service
	) {}

	/** ERC-4361の署名用メッセージ（nonce発行を含む）を発行する */
	public function handle( Address $address, ChainId $chain_id ): Erc4361Message {
		// 古いnonceデータを削除
		// ※ タイミングはどこでも良いが、リクエストも多いのでここで削除する
		$this->erc4361_service->purgeExpiredNonceData();

		$message = $this->transaction_runner->transactional(
			function () use ( $address, $chain_id ) {
				// nonceを生成し、アドレスと紐づけて保存
				$nonce = $this->erc4361_nonce_generator->generate();
				$this->erc4361_nonce_repository->save( $address, $nonce );

				// 署名用メッセージを生成
				return $this->erc4361_service->createMessage( $address, $chain_id, $nonce );
			}
		);

		// トランザクション内で記録するとロールバック時に消失するため、確定後に記録する
		// nonce の値は機密値のため記録しない
		$this->app_log_service->info( "[139E270B] ERC-4361 message issued. address: {$address}, chainId: {$chain_id}" );

		return $message;
	}
}
